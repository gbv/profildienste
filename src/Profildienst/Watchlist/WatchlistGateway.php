<?php

namespace Profildienst\Watchlist;

interface WatchlistGateway {

    public function getWatchlistData($watchlistId);
    public function getWatchlists();
    public function createWatchlist(array $watchlistData);
    public function deleteWatchlist($watchlistId);
    public function renameWatchlist($watchlistId, $name);
    public function updateDefaultWatchlist($watchlistId);
    public function removeAllTitlesFromWatchlist($watchlistId);
    public function updateWatchlists(array $watchlists);
}