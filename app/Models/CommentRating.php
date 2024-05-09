<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentRating extends Model
{
    /**
     * @var string[]
     */
    public $fillable = ['rating_type', 'comment', 'rating'];

    /**
     * @var string
     */
    public $table = 'comment_ratings';

    public function rating_type(){
        return $this->hasOne(RatingType::class, 'id','rating_type');
    }
}
