<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
