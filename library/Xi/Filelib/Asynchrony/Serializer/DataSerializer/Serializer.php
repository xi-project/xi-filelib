<?php

namespace Xi\Filelib\Asynchrony\Serializer\DataSerializer;

use Xi\Filelib\Asynchrony\Serializer\SerializedIdentifiable;

interface Serializer
{
    public function deserializeCallee($class);

    public function deserializeIdentifiable(SerializedIdentifiable $identifiable);
}
