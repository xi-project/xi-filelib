<?php

namespace Xi\Filelib;

class Events
{
    const FILE_BEFORE_CREATE = 'xi_filelib.file.before_create';
    const FILE_AFTER_CREATE = 'xi_filelib.file.create';
    const FILE_AFTER_AFTERUPLOAD = 'xi_filelib.file.after_upload';
    const FILE_AFTER_DELETE = 'xi_filelib.file.delete';
    const FILE_AFTER_COPY = 'xi_filelib.file.copy';
    const FILE_AFTER_UPDATE = 'xi_filelib.file.update';
    const FILE_AFTER_RENDER = 'xi_filelib.file.render';

    const RESOURCE_AFTER_DELETE = 'xi_filelib.resource.delete';

    const FOLDER_AFTER_DELETE = 'xi_filelib.folder.delete';
    const FOLDER_AFTER_CREATE = 'xi_filelib.folder.create';
    const FOLDER_AFTER_UPDATE = 'xi_filelib.folder.update';

    const PROFILE_AFTER_ADD = 'xi_filelib.profile.add';

    const PLUGIN_AFTER_ADD = 'xi_filelib.plugin.add';

    const IDENTITYMAP_AFTER_ADD = 'xi_filelib.identitymap.after_add';
    const IDENTITYMAP_BEFORE_ADD = 'xi_filelib.identitymap.before_add';
    const IDENTITYMAP_AFTER_REMOVE = 'xi_filelib.identitymap.after_remove';
    const IDENTITYMAP_BEFORE_REMOVE = 'xi_filelib.identitymap.before_remove';
}
