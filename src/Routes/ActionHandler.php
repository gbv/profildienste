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
                if (!$allow($title->getStatus())) {
                    throw new UserErrorException('This action is not allowed on the selection of titles!');
                }
            }

            return $this->titleRepository->changeStatusOfTitles($affected, $newStatus) ? $affected : null;

        } else {

            if ($affected === 'overview') {
                $affected = 'normal';
            }

            $affectedState = preg_match('/watchlist\/.*/', $affected) ? 'watchlist' : $affected;

            if (!$allow($affectedState)) {
                throw new UserErrorException('This action is not allowed on the selection of titles!');
            }

            return $this->titleRepository->changeStatusOfView($affected, $newStatus) ? $affected : null;
        }

    }

}