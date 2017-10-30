# Project Title

A try to write a more "talkative" FTP Client. Since the internal PHP functions do not output anything useful on problems, I wrote something which does. 
**Attention: This class is early alpha, has no error management and only a few basic functions.**

### Prerequisites

* Apache Webserver
* \>= PHP 7.1.1 

## Running

```php
$ftp = new FTP('ftp.rz.uni-wuerzburg.de');
$ftp->startPassiveMode();
$ftp->listFiles();
$ftp->disconnect();
```

## Built With

* [PHP](http://php.net) - Programming language used

## Author

**Philipp Palmtag** - *Initial work* - [ppalmtag](https://github.com/ppalmtag)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
