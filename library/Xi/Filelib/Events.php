<?php

namespace Xi\Filelib;

class Events
{
    const FILE_BEFORE_CREATE = 'xi_filelib.file.before_create';
    const FILE_AFTER_CREATE = 'xi_filelib.file.after_create';
    const FILE_AFTER_AFTERUPLOAD = 'xi_filelib.file.after_upload';
    const FILE_AFTER_DELETE = 'xi_filelib.file.after_delete';
    const FILE_AFTER_COPY = 'xi_filelib.file.after_copy';
    const FILE_AFTER_UPDATE = 'xi_filelib.file.after_update';
    const FILE_AFTER_RENDER = 'xi_filelib.file.after_render';

    const RESOURCE_AFTER_DELETE = 'xi_filelib.resource.after_delete';

    const FOLDER_AFTER_DELETE = 'xi_filelib.folder.after_delete';
    const FOLDER_AFTER_CREATE = 'xi_filelib.folder.after_create';
    const FOLDER_AFTER_UPDATE = 'xi_filelib.folder.after_update';

    const PROFILE_AFTER_ADD = 'xi_filelib.profile.after_add';

    const PLUGIN_AFTER_ADD = 'xi_filelib.plugin.after_add';

    const IDENTITYMAP_AFTER_ADD = 'xi_filelib.identitymap.after_add';
    const IDENTITYMAP_BEFORE_ADD = 'xi_filelib.identitymap.before_add';
    const IDENTITYMAP_AFTER_REMOVE = 'xi_filelib.identitymap.after_remove';
    const IDENTITYMAP_BEFORE_REMOVE = 'xi_filelib.identitymap.before_remove';
}
