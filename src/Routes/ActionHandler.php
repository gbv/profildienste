<?php

namespace Routes;

use Exceptions\UserErrorException;

trait ActionHandler {

    private function validateAffectedTitles($request) {

        $parameters = $request->getParsedBody();

        $affected = $parameters['affected'];

        if (is_null($affected) || empty($affected)) {
            throw new UserErrorException('At least one title must be affected by the change!');
        }

        return $affected;
    }

    private function handleStatusChange($request, $newStatus, $allow) {

        $affected = $this->validateAffectedTitles($request);

        if (is_array($affected)) {

            $titles = $this->titleRepository->findTitlesById($affected);

            if (count($titles) === 0) {
                throw new UserErrorException('No titles with the given IDs found!');
            }

            foreach ($titles as $title) {
                if (!$allow($title->getStatus(), $title->isInWatchlist())) {
                    throw new UserErrorException('This action is not allowed on the selection of titles!');
                }
            }

            return $this->titleRepository->changeStatusOfTitles($affected, $newStatus) ? $affected : null;

        } else {

            if ($affected === 'overview') {
                $affected = 'normal';
            }

            if (!$allow($newStatus, null)) {
                throw new UserErrorException('This action is not allowed on the selection of titles!');
            }

            if (preg_match('/watchlist\/(.*)/', $affected, $match)) {
                $watchlistId = $match[1] ?? null;

                if(is_null($watchlistId)){
                    throw new UserErrorException('Invalid watchlist format');
                }

                return $this->titleRepository->changeStatusOfWatchlist($watchlistId, $newStatus) ? $affected : null;
            } else {
                return $this->titleRepository->changeStatusOfView($affected, $newStatus) ? $affected : null;
            }
        }


    }

}