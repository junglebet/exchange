<?php

namespace App\Models\User;

use App\Jobs\QueuedVerifyEmailJob;
use App\Models\User\Traits\Relations\UserRelation;
use App\Models\User\Traits\Scopes\UserScope;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;
    use UserRelation;
    use UserScope;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'referral_id',
        'referral_code',
        'deactivated',
        'email_verified_at',
        'kyc_verified_at',
        'deleted',
        'google_id',
        'twitter_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'current_team_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => "datetime:Y-m-d H:i:s",
        'deactivated' => 'boolean',
    ];

    protected $appends = [
        'email_verified',
        'kyc_verified',
    ];

    public function getEmailVerifiedAttribute() {
        return (bool)$this->email_verified_at;
    }

    public function getKycVerifiedAttribute() {
        return (bool)$this->kyc_verified_at;
    }

    public function getEmailAttribute($value) {
        return $this->deleted ? 'Deleted' : $value;
    }

    public function sendEmailVerificationNotification()
    {
        QueuedVerifyEmailJob::dispatch($this);
    }
}

