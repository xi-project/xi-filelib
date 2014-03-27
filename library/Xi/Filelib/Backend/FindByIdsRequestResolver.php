<?php

namespace Xi\Filelib\Backend;

interface FindByIdsRequestResolver
{
    /**
     * @param FindByIdsRequest $request
     * @return FindByIdsRequest
     */
    public function findByIds(FindByIdsRequest $request);

    /**
     * Returns whether the resolver is an origin resolver (like a platform) and
     * thus should trigger an instantiate event.
     *
     * @return bool
     */
    public function isOrigin();

}
