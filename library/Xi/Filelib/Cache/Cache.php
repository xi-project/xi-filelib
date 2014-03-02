<?php

namespace Xi\Filelib\Cache;

use Xi\Filelib\IdentityMap\Identifiable;

interface Cache
{
    public function findById($id, $className);

    public function findByIds(array $ids = array(), $className);

    public function saveMany($identifiables);

    public function deleteMany($identifiables);

    public function save(Identifiable $identifiable);

    public function delete(Identifiable $identifiable);
}

