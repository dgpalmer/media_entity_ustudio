## About Media entity

Media entity provides a 'base' entity for a media element. This is a very basic
entity which can reference to all kinds of media-objects (local files, YouTube
videos, tweets, CDN-files, ...). This entity only provides a relation between
Drupal (because it is an entity) and the resource. You can reference to this
entity within any other Drupal entity.

## About Media entity uStudio

This module provides uStudio integration for Media entity (i.e. media type provider
plugin).

### uStudio API
This module uses uStudio oembed API to fetch the uStudio html and all the metadata.
You will need to:

- Create a Media bundle with the type provider "uStudio".
- On that bundle create a field for the uStudio url/source (this should be a plain text or link field).
- Return to the bundle configuration and set "Field with source information" to use that field.

### Storing field values
If you want to store the fields that are retrieved from uStudio you should create appropriate fields on the created media bundle (id) and map this to the fields provided by uStudio.php.

**NOTE:** At the moment there is no GUI for that, so the only method of doing that for now is via CMI.

This would be an example of that (the field_map section):

```
langcode: en
status: true
dependencies:
  module:
    - media_entity_ustudio
id: ustudio
label: uStudio
description: 'uStudio photo/video to be used with content.'
type: ustudio
type_configuration:
  source_field: link
field_map:
  id: ustudio_id
  type: ustudio_type
  thumbnail: ustudio_thumbnail
  username: ustudio_username
  caption: ustudio_caption
```

Project page: http://drupal.org/project/media_entity_ustudio

Maintainers:
 - Donovan Palmer (@dpiga) drupal.org/user/332471
