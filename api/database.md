
```
CREATE TABLE `IMG` (
  'id' int(11) NOT NULL AUTO_INCREMENT,
  'phash' bigint(20) unsigned NOT NULL,
  'mhash' varchar(145) COLLATE utf8_unicode_ci NOT NULL,
  'name' varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  'directory' varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  'author' varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  'license' varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  'url' varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  'imageurl' varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  'source' varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  'dateuploaded' datetime NOT NULL,
  'dateuploadu' int(11) NOT NULL,
  'title' varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  'deleted' varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'n',
  'reasons' varchar(1024) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'none',
  'falsePositives' varchar(2048) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'none',
  'comments' varchar(1024) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'none',
  PRIMARY KEY ('id')
) ENGINE=InnoDB AUTO_INCREMENT=414514 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```
