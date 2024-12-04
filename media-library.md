# Spatie - Laravel Media Library Package

## Documentation link
https://spatie.be/docs/laravel-medialibrary/v11/introduction



## Preparing the model
---
1. On the model php file, add the trait: "InteractsWithMedia"
2. On the class function, add: "implements HasMedia"
3. Add a function for additional conversions needed (e.g. thumbnails)

`public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumbnail')
        ->fit(Fit::Fill, 250, 250)
        ->optimize()
        ->nonQueued();
}`



## Steps for uploading an image
---
1. Use livewire WithFileUploads, then do normal file uploads, import trait WithImageUploading
2. On call of function, loop each file
3. Create/Retrieve the model associated with the file (e.g. Product, Service, etc.)
4. Call the helper to link the file with the model:

`$this->upload_file_media($model, $file, $collection)`

## Copying media (Trait - WithImageUploading)
---
1. Get the model to have its media files duplicated.
2. Use `$medias = $yourModel->getMedia()` to get the collection of medias.
3. Loop thru the collection.
4. Foreach media file, use `$this->copy_file_media($newModel, $media, $collection)`



## Accessing the file
---
# First media
1. Retrieve the model
2. Use the following:

- url of media `->getFirstMediaUrl($collection = null, $conversionType = null)`
- media model `->getFirstMedia($collection = null)`

# Collection of media
1. Retrieve the model
2. Use `$mediaItems = $yourModel->getMedia($collection = null)`
3. Access each media

# Media from S3
> Upon getting the media data, use `$mediaItems[0]->getTemporaryUrl(Carbon $deadline)`


## Note
- Deletion of model, also auto-deletes the associated media data and actual files