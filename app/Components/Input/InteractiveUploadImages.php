<?php

namespace App\Components\Input;

use App\Traits\WithImageUploading;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;
use Livewire\WithFileUploads;

class InteractiveUploadImages extends Component
{
    use WithImageUploading, WithFileUploads;
    public $uploaded_images = [];
    #[Locked]
    public $function_name;
    public $deleted_images = [];
    public $images = [];
    public $image_count = 0;
    public $max_images;
    public $replacement_image;
    public $replace_image_index;
    public $color;

    public function mount($uploaded_images = [], $max, $function, $color = 'text-rp-red-500')
    {
        $this->uploaded_images = $uploaded_images;
        $this->max_images = $max;
        $this->function_name = $function;
        $this->color = $color;
    }

    private function format_images()
    {
        $uploaded_images = [];

        foreach ($this->uploaded_images as $key => $image) {
            $uploaded_images[$key]['name'] = $image['name'];
            $uploaded_images[$key]['image'] = $image['id'] ? $image['image'] : $image['image']->getRealPath();
            $uploaded_images[$key]['size'] = $image['size'];
            $uploaded_images[$key]['id'] = $image['id'];
            $uploaded_images[$key]['order'] = $image['order'];
        }

        return $uploaded_images;
    }

    /**
     * When uploading images, the receiver of the dispatch
     * event should manipulate the uploaded image to have
     * a new TemporaryUploadedFile instance.
     * @param mixed $images
     * @return void
     */
    public function updatedImages($images)
    {
        $this->validate([
            'images' => 'array|max:' . $this->max_images - count($this->uploaded_images),
            'images.*' => 'image|mimes:png,jpg,jpeg|max:5120',
        ], [
            'images.max' => 'Only ' . $this->max_images . ($this->max_images > 1 ? ' images' : ' image') . '  allowed.',
            'images.*.image' => 'The uploaded file must be a png, jpg or jpeg image.',
            'images.*.mimes' => 'The uploaded file must be a png, jpg or jpeg image.',
            'images.*.max' => 'The uploaded file must be less than 5MB.',
        ]);

        foreach ($images as $image) {
            $this->uploaded_images[] = [
                'name' => $image->getClientOriginalName(),
                'image' => $image,
                'size' => $this->format_file_size($image->getSize()),
                'id' => null,
                'order' => count($this->uploaded_images) + 1
            ];
        }
        $this->dispatch($this->function_name, $this->format_images());
    }


    public function removeImage($index)
    {
        if (!isset($this->uploaded_images[$index])) {
            session()->flash('error', 'Image not found.');
            return;
        }

        if ($this->uploaded_images[$index]['id'] != null) {
            $this->deleted_images[] = $this->uploaded_images[$index];
            $this->dispatch('updateDeletedImages', $this->deleted_images);
        }

        array_splice($this->uploaded_images, $index, 1);
        $this->image_count--;

        foreach ($this->uploaded_images as $key => $image) {
            $this->uploaded_images[$key]['order'] = $key;
        }

        $this->dispatch($this->function_name, $this->format_images());
    }

    public function sortImages($oldIndex, $newIndex)
    {
        $image = $this->uploaded_images[$oldIndex];

        array_splice($this->uploaded_images, $oldIndex, 1);
        array_splice($this->uploaded_images, $newIndex, 0, [$image]);

        foreach ($this->uploaded_images as $key => $image) {
            $this->uploaded_images[$key]['order'] = $key + 1;
        }

        $this->dispatch($this->function_name, $this->format_images());
    }

    public function replaceImage($index)
    {
        $this->replace_image_index = $index;
    }

    public function updatedReplacementImage()
    {
        if ($this->replace_image_index === null) {
            session()->flash('error', 'Image not found.');
            return;
        }

        $this->validate([
            'replacement_image' => 'image|mimes:png,jpg,jpeg|max:5120',
        ], [
            'replacement_image.image' => 'The uploaded file must be a png, jpg or jpeg image.',
            'replacement_image.mimes' => 'The uploaded file must be a png, jpg or jpeg image.',
            'replacement_image.max' => 'The uploaded file must be less than 5MB.',
        ]);

        if ($this->uploaded_images[$this->replace_image_index]['id'] != null) {
            $this->deleted_images[] = $this->uploaded_images[$this->replace_image_index];
            $this->dispatch('updateDeletedImages', $this->deleted_images);
        }

        $this->uploaded_images[$this->replace_image_index] = [
            'name' => $this->replacement_image->getClientOriginalName(),
            'image' => $this->replacement_image,
            'size' => $this->format_file_size($this->replacement_image->getSize()),
            'id' => null,
            'order' => $this->replace_image_index - 1
        ];

        $this->dispatch($this->function_name, $this->format_images());
    }

    #[On('resetImage')]
    public function resetImage($function_name)
    {
        if ($function_name == $this->function_name) {
            $this->uploaded_images = [];
            $this->deleted_images = [];
            $this->image_count = 0;
        }
    }

    public function render()
    {
        return view('components.input.interactive-upload-images');
    }
}
