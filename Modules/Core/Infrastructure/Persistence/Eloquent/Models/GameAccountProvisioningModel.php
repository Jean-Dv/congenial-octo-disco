<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class GameAccountProvisioningModel extends Model
{
    protected $table = 'game_account_provisionings';

    protected $fillable = [
        'user_id',
        'realm_id',
        'game_username',
        'status',
        'attempts',
        'last_error',
    ];

    protected $casts = [
        'attempts' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    public function realm()
    {
        return $this->belongsTo(RealmModel::class, 'realm_id');
    }
}
