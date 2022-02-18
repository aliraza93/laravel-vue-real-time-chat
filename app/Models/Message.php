<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Message extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $guarded=[];
    protected $dates = ['sentMessageOn'];
    protected $appends = ['SentMessageOnHumanReadable', 'file'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getSentMessageOnHumanReadableAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function registerMediaConversions(?Media $media = null) : void
    {
        $this->addMediaConversion('thumb')
              ->width(100)
              ->height(100);
    }

    public function getFileAttribute()
    {
        if($this->media('chat')->first()) {
            $file = $this->getMedia('chat')->first();
            $file->thumbnail = substr($file->mime_type, 0, 5) == 'image' ? $file->getUrl('thumb') : null;
            return $file;
        }
        return null;
    }

    public function getImageAttribute()
    {
        if($this->media('chat')->first()) {
            return substr($this->file->mime_type, 0, 5) == 'image';
        }
        return null;
    }

}
