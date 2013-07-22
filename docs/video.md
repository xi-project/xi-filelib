# Video processing with Filelib

<small>Author: [Peter Hillerström](http://composed.nu/peterhil) [[Github]](https://github.com/peterhil)</small>


## Filelib provides two plugins for processing video files.

- <a href="#ffmpeg">FFmpegPlugin</a>
- <a href="#zencoder">ZencoderPLugin</a>


## <a name="ffmpeg">FFmpegPlugin</a>

Calls `ffmpeg` with the specified configuration upon video
upload.

Uses Symfony
[Process](http://symfony.com/doc/current/components/process.html)
component to call command line utilities `ffmpeg` and `ffprobe`
with the options configured in Filelib configuration upon a video upload.

The plugin uses FFmpegHelper class, which maps the Filelib
configuration options
to POSIX style command line arguments for `ffmpeg` and `ffprobe`.


### Requirements

[FFmpeg](http://ffmpeg.org/download.html) version 0.9 or newer. Older
ones are missing the JSON output from `ffprobe`.
You need to install [FFmpeg with LGPL license](http://ffmpeg.org/legal.html), for it to be compatible with the [Filelib's BSD license](https://github.com/xi-project/xi-filelib/blob/master/LICENSE).

On Mac OS X you do this easily with MacPorts:

```
sudo port install ffmpeg-devel@-gpl2-nonfree
```

### Configuration

Example:

    xi_filelib:
        ...
        profiles:
            video:
                identifier: video
                description: 'Video'
                accessToOriginal: false
                linker:
                    type: Xi\Filelib\Linker\SequentialLinker # Can use other types of linkers too
                    options:
                        filesPerDirectory: 500
                        directoryLevels: 3
    	plugins:
    	        ffmpeg:
                identifier: ffmpeg
                type: Xi\Filelib\Plugin\Video\FFmpeg\FFmpegPlugin
                profiles: [video]
                command: 'ffmpeg' # Optional, can be an absolute path
                options:
                    y: true # Overwrite existing files
                    loglevel: warning
    				# You may need to set the following values for larger videos:
                    # probesize: 100 # = 100 Mb, defaults to 5 Mb
                    # analyzeduration: 10000000 # = 10 s, defaults to 5 s
                inputs:
                    original:
                        filename: true # Will be replaced by the uploaded file. Can be repeated on input filenames.
                        options:
                            ss: '00:00:01.000' # Seek to one second
                            r: 1 # Rate is 1 frames per second
                            vframes: 1 # Process one video frame
                outputs:
                    1080p_still:
                        filename: 1080p_still.jpg
                        options:
                            s: '1920x1080'
                            vframes: 1
                    720p_still:
                        filename: 720p_still.jpg
                        options:
                            s: '1280x720'
                            vframes: 1
                    480p_still:
                        filename: 480p_still.jpg
                        options:
                            s: '854x480'
                            vframes: 1
                    135_thumb:
                        filename: 135_thumb.jpg
                        options:
                            s: '240x135'
                            vframes: 1

To create the output `1080p_still` for an uploaded video called `Manatees.mp4`,
the above example will call `ffmpeg` with the following options:

    ffmpeg -y -loglevel 'warning' -ss '00:00:01.000' -r 1 -vframes 1 -i 'Manatees.mp4' \
        -s '1920x1080' -vframes 1 'filelib/public/path/id/1080p_still.jpg'


### Limitations

Only saves *one* output file per configured output.
In other words, the numbered output files are not supported for now.



## <a name="zencoder">ZencoderPlugin</a>


### Requirements

- [RabbitMQ](http://www.rabbitmq.com/) or some other message queue implementing the [AMQP protocol](http://en.wikipedia.org/wiki/Advanced_Message_Queuing_Protocol).
- [Zencoder](http://zencoder.com/) account and the [zencoder-php](https://github.com/zencoder/zencoder-php) library.
- [Amazon S3](http://aws.amazon.com/s3/) account and the
  [ZendService\Amazon](https://github.com/zendframework/ZendService_Amazon)
  component, which has been broken since Zend 2.0.0-beta versions, but the version
  2.0.3 will be fixed.
  Meanwhile, see [my branch](https://github.com/peterhil/ZendService_Amazon/tree/fix-s3-client) for the fixes.
  On Filelib version 0.7, the older [version 1.1.2
  of the Zend Service Amazon S3](http://framework.zend.com/manual/1.12/en/zend.service.amazon.html) used to work.


### Installation

Install the requirements with Composer:

    php composer.phar install --dev

Or copy the required Php-amqplib, Zencoder-php and Zend components into your composer:

    {
        // other stuff removed
        "repositories": {
            "zendframework": {
                "type": "composer",
                "url": "http://packages.zendframework.com/"
            },
            "zencoder-php": {
                "type": "package",
                "package": {
                    "name": "zencoder/zencoder-php",
                    "version": "2.0.2",
                    "source": {
                        "url": "git://github.com/zencoder/zencoder-php.git",
                        "type": "git",
                        "reference": "v2.0.2"
                    }
                }
            }
        },
        "require": {
            "videlalvaro/php-amqplib": ">=dev-master",
            "zencoder/zencoder-php": "2.0.2",
            "zendframework/zendservice-amazon": "2.0.*",
            "zendframework/zend-i18n": "2.0.*",
            "zendframework/zend-filter": "2.0.*",
            "zendframework/zend-servicemanager": "2.0.*"
        }
    }
