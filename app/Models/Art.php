<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use OpenApi\Annotations as OA;

/**
 * Class Art.
 * 
 * @author  Dareen <dareen.422023008@civitas.ukrida.ac.id>
 * 
 * @OA\Schema(
 *     description="Art model",
 *     title="Art model",
 *     required={"art_name", "type"},
 *     @OA\Xml(
 *         name="Art"
 *     )
 * )
 */

 
class Art extends Model
{
    // use HasFactory;
    use SoftDeletes;
    protected $table = 'arts';
    protected $fillable = [
        'art_name',
        'artist',
        'techniques',
        'type',
        'size',
        'cover',
        'description',
        'price',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    public function data_adder(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}