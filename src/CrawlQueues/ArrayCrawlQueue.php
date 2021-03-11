<?php

namespace Spatie\Crawler\CrawlQueues;

use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlUrl;
use Spatie\Crawler\Exceptions\InvalidUrl;
use Spatie\Crawler\Exceptions\UrlNotFoundByIndex;

class ArrayCrawlQueue implements CrawlQueue
{
    /**
     * All known URLs, indexed by URL string.
     *
     * @var CrawlUrl[][]
     */
    protected array $urls = [];

    /**
     * All known URLs, indexed by URL ID.
     *
     * @var CrawlUrl[]
     */
    protected array $urlsByID = [];

    /**
     * Pending URLs, indexed by URL ID.
     *
     * @var CrawlUrl[]
     */
    protected array $pendingUrls = [];

    public function add(CrawlUrl $crawlUrl): CrawlQueue
    {
        $urlID = $crawlUrl->getId();
        $stringUrl = (string)$crawlUrl->url;
        $urlMarker = !is_null($crawlUrl->foundOnUrl) ? (string)$crawlUrl->foundOnUrl : 0;

        if (! isset($this->urls[$urlID][$urlMarker])) {
            $this->urls[$stringUrl][$urlMarker] = $crawlUrl;
            $this->pendingUrls[$urlID] = $crawlUrl;
            $this->urlsByID[$urlID] = $crawlUrl;
        }

        return $this;
    }

    public function hasPendingUrls(): bool
    {
        return (bool) $this->pendingUrls;
    }

    public function getUrlById($id): CrawlUrl
    {
        if (!isset($this->urlsByID[$id])) {
            throw new UrlNotFoundByIndex("Crawl url {$id} not found in collection.");
        }

        return $this->urlsByID[$id];
    }

    public function hasAlreadyBeenProcessed(CrawlUrl $crawlUrl): bool
    {
        $urlID = $crawlUrl->getId();
        $stringUrl = (string)$crawlUrl->url;
        $urlMarker = !is_null($crawlUrl->foundOnUrl) ? (string)$crawlUrl->foundOnUrl : 0;

        if (isset($this->pendingUrls[$urlID])) {
            return false;
        }

        if (isset($this->urls[$stringUrl][$urlMarker])) {
            return true;
        }

        return false;
    }

    public function markAsProcessed(CrawlUrl $crawlUrl): void
    {
        $urlID = $crawlUrl->getId();

        unset($this->pendingUrls[$urlID]);
    }

    public function getProcessedUrlCount(): int
    {
        return count($this->urls) - count($this->pendingUrls);
    }

    /**
     * @param CrawlUrl $crawlUrl
     *
     * @return bool
     */
    public function has(CrawlUrl $crawlUrl): bool
    {
        $stringUrl = (string)$crawlUrl->url;
        $urlMarker = !is_null($crawlUrl->foundOnUrl) ? (string)$crawlUrl->foundOnUrl : 0;

        return isset($this->urls[$stringUrl][$urlMarker]);
    }

    public function getPendingUrl(): ?CrawlUrl
    {
        foreach ($this->pendingUrls as $pendingUrl) {
            return $pendingUrl;
        }

        return null;
    }
}
