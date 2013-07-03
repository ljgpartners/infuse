# Transit v1.3.5 [![Build Status](https://travis-ci.org/milesj/Transit.png?branch=master)](https://travis-ci.org/milesj/Transit) #

A lightweight file uploader that also provides extended support for file validation,
file transformation (image resizing, cropping, etc) and file transportation (moving
files to Amazon S3 or another external storage system).

## Requirements ##

* PHP 5.3.3
	* Fileinfo
	* Multibyte
	* Curl
	* Exif (optional)
* Composer
	* AWS PHP SDK v2.2+

## Features ##

* Easily upload a file into the local file system
* Basic support for file moving and renaming through `File`
* Overwrite protection and file name filtering
* Import a file from a remote location, local file system path or an input stream
* Transform and alter a file by running a `Transformer` on it
* Create new files based off an original file by using transformers
* Transport to or delete a file from Amazon S3 or Glacier using a `Transporter`
* Validate files and images using a defined set of rules using a `Validator`
* Support for extending built in transporters, transformers and validators
* Exif reading support through `File` and orientation fixing through `ExifTransformer`

## Documentation ##

Thorough documentation can be found here (eventually): http://milesj.me/code/php/transit