<?php

namespace SellNow\Models;

use App\Core\Contracts\Authenticatable;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Exception;

class User extends EloquentModel implements Authenticatable
{
    protected $table = 'users';
    protected $fillable = ['username', 'email', 'password'];
    protected $hidden = ['password'];

    public static function find(int $id): ?self
    {
        try {
            return static::query()->find($id);
        } catch (Exception $e) {
            // Fallback to basic query if there's an issue
            return static::where('id', $id)->first();
        }
    }

    public function getAuthId(): int
    {
        return (int) $this->id;
    }

    public function getAuthPassword(): string
    {
        return $this->password;
    }

    /**
     * @throws Exception
     */
    public static function findByCredentials(array $credentials): ?self
    {
        if (!isset($credentials['email'])) {
            return null;
        }

        return static::where('email', $credentials['email'])->first();
    }

    public function getUsername()
    {
        return $this->username;
    }

    // Relationships
    public function products()
    {
        return $this->hasMany(Product::class, 'user_id');
    }
}