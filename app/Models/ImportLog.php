<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ImportLog extends Model
{
    protected $fillable = ['user_id','file_name','total_rows','success_rows','failed_rows','errors'];
    protected $casts = ['errors' => 'array'];
    public function user() { return $this->belongsTo(User::class); }
}
