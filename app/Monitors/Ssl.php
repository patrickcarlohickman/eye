<?php

namespace Eyewitness\Eye\Monitors;

use Exception;
use Eyewitness\Eye\Repo\History\Ssl as History;
use Illuminate\Support\Facades\Cache;
use Eyewitness\Eye\Notifications\Messages\Ssl\Invalid;
use Eyewitness\Eye\Notifications\Messages\Ssl\Revoked;
use Eyewitness\Eye\Notifications\Messages\Ssl\Expiring;
use Eyewitness\Eye\Notifications\Messages\Ssl\GradeChange;

class Ssl extends BaseMonitor
{
    /**
     * Poll the SSL for its checks.
     *
     * @return void
     */
    public function poll()
    {
        if (! count(config('eyewitness.application_domains'))) {
            $this->eye->logger()->debug('No application domain set for SSL monitor');
            return;
        }

        foreach (config('eyewitness.application_domains') as $domain) {
            if ($this->isValidDomain($domain)) {
                $this->startScan($domain);
            }
        }
    }

    /**
     * Determine the SSL scan results.
     *
     * @return void
     */
    public function result()
    {
        if (! count(config('eyewitness.application_domains'))) {
            $this->eye->logger()->debug('No application domain set for SSL monitor');
            return;
        }

        foreach (config('eyewitness.application_domains') as $domain) {
            $result = $this->getResults($domain);

            if ($result) {
                $this->checkForSslRecordChanges($domain, $result);
                $this->saveSslRecord($domain, $result);
            }
        }
    }

    /**
     * Start the scan for a given domain for a valid SSL certificate against the API.
     *
     * @param  string  $domain
     * @return void
     */
    protected function startScan($domain)
    {
        $result = $this->eye->api()->sslStart($domain);

        if (isset($result['multiple_ips']) && isset($result['token'])) {
            $result = $this->eye->api()->sslStart($domain, $result['multiple_ips'], $result['token']);
        }

        if (isset($result['error'])) {
            return $this->eye->logger()->error('SSL API scan error', $result['error'], $domain);
        }

        if (($result['status_id'] == "1") && (isset($result['job_id']))) {
            Cache::put('eyewitness_ssl_job_id_'.$domain, $result['job_id'], 50);
        } else {
            $this->eye->logger()->error('SSL API invalid result', print_r($result, true), $domain);
        }
    }

    /**
     * Get the scan results from the API.
     *
     * @param  string  $domain
     * @return array|null
     */
    protected function getResults($domain)
    {
        if (! Cache::has('eyewitness_ssl_job_id_'.$domain)) {
            return null;
        }

        $result = $this->eye->api()->sslResult(Cache::pull('eyewitness_ssl_job_id_'.$domain));

        if (isset($result['error'])) {
            return $this->eye->logger()->error('SSL API result error', $result['error'], $domain);
        }

        return $result;
    }

    /**
     * Check if the supplied result is different from the most recently stored
     * SSL record available.
     *
     * @param  string  $domain
     * @param  array  $result
     * @return void
     */
    protected function checkForSslRecordChanges($domain, $result)
    {
        if (! $ssl_history = History::where('meta', $domain)->first()) {
            return;
        }

        if ($ssl_history->record['valid'] !== $result['certificates']['information'][0]['valid_now']) {
            if (! $result['certificates']['information'][0]['valid_now']) {
                $this->eye->status()->setSick('ssl_'.$domain);
                return $this->eye->notifier()->alert(new Invalid(['domain' => $domain]));
            }
        }

        if ($ssl_history->record['revoked'] !== $result['certificates']['information'][0]['revoked']) {
            if ($result['certificates']['information'][0]['revoked']) {
                $this->eye->status()->setSick('ssl_'.$domain);
                return $this->eye->notifier()->alert(new Revoked(['domain' => $domain]));
            }
        }

        if ($ssl_history->record['expires_soon'] !== $result['certificates']['information'][0]['expires_soon']) {
            if ($result['certificates']['information'][0]['expires_soon']) {
                $this->eye->status()->setSick('ssl_'.$domain);
                return $this->eye->notifier()->alert(new Expiring(['domain' => $domain,
                                                                   'valid_to' => $result['certificates']['information'][0]['valid_to']]));
            }
        }

        if ($ssl_history->record['grade'] !== $result['results']['grade']) {
            return $this->eye->notifier()->alert(new GradeChange(['domain' => $domain,
                                                                  'old_grade' => $ssl_history->record['grade'],
                                                                  'new_grade' => $result['results']['grade']]));
        }

        $this->eye->status()->setHealthy('ssl_'.$domain);
    }

    /**
     * Save the SSL record to the database.
     *
     * @param  string  $domain
     * @param  array  $result
     * @return void
     */
    protected function saveSslRecord($domain, $result)
    {
        $record['grade'] = $result['results']['grade'];
        $record['results_url'] = 'https://www.htbridge.com/ssl/?id=' . $result['internals']['short_id'];

        if (isset($result['certificates']['information'][0])) {
            $record['valid'] = $result['certificates']['information'][0]['valid_now'];
            $record['valid_from'] = $result['certificates']['information'][0]['valid_from'];
            $record['valid_to'] = $result['certificates']['information'][0]['valid_to'];
            $record['revoked'] = $result['certificates']['information'][0]['revoked'];
            $record['expires_soon'] = $result['certificates']['information'][0]['expires_soon'];
            $record['issuer'] = $result['certificates']['information'][0]['issuer_cn'];
        }

        try {
            History::where('meta', $domain)->delete();
            History::create(['type' => 'ssl', 'meta' => $domain, 'record' => $record]);
        } catch (Exception $e) {
            $this->eye->logger()->error('Ssl record save failed', $e, $domain);
            throw $e;
        }
    }

    /**
     * Check if the URL is valid.
     *
     * @param  string  $domain
     * @return bool
     */
    protected function isValidDomain($domain)
    {
        if (filter_var($domain, FILTER_VALIDATE_URL) === false) {
            $this->eye->logger()->debug('SSL URL not valid', $domain);
            return false;
        }

        return true;
    }
}
