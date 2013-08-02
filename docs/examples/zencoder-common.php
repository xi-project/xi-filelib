<?php

use Xi\Filelib\Plugin\Video\ZencoderPlugin;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Publisher\Adapter\Filesystem\SymlinkFilesystemPublisherAdapter;
use Xi\Filelib\Publisher\Linker\CreationTimeLinker;
use Xi\Filelib\Publisher\AutomaticPublisherPlugin;

$zencoderPlugin = new ZencoderPlugin(
    'zencoder',
    ZENCODER_KEY,
    S3_KEY,
    S3_SECRETKEY,
    ZENCODER_BUCKET,
    array(
        '720p_webm' => array(
            'extension' => 'webm',
            'output' => array(
                'label' => '720p_webm',
                'format' => 'webm',
                'video_codec' => 'vp8',
                'audio_codec' => 'vorbis',
                'size' => '1280x720',
                'aspect_mode' => 'preserve',
                'deinterlace' => 'detect',
                'max_video_bitrate' => 3600,
                'max_frame_rate' => 25,
                'keyframe_interval' => 50,
                'audio_bitrate' => 224,
                'audio_channels' => 2,
                'audio_sample_rate' => 44100
            )
        ),
        '720p_ogv' => array(
            'extension' => 'ogv',
            'output' => array(
                'label' => '720p_ogv',
                'format' => 'ogv',
                'video_codec' => 'theora',
                'audio_codec' => 'vorbis',
                'size' => '1280x720',
                'aspect_mode' => 'preserve',
                'deinterlace' => 'detect',
                'max_video_bitrate' => 3600,
                'max_frame_rate' => 25,
                'keyframe_interval' => 50,
                'audio_bitrate' => 224,
                'audio_channels' => 2,
                'audio_sample_rate' => 44100
            )
        ),
    )
);
$filelib->addPlugin($zencoderPlugin);

$publisher = new Publisher(
    new SymlinkFilesystemPublisherAdapter(__DIR__ . '/web/files', '600', '700', 'files'),
    new CreationTimeLinker()
);
$publisher->attachTo($filelib);

$automaticPublisherPlugin = new AutomaticPublisherPlugin($publisher);
$filelib->addPlugin($automaticPublisherPlugin);

