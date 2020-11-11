<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;
use Uuid;

class Client extends Authenticatable {

    use HasFactory, Notifiable, HasApiTokens;

    public $incrementing = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'surname',
        'gender',
        'birth_year',
        'email',
        'password',
        'cel',
        'tel',
        'role'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function boot() {

        parent::boot();
        self::creating(function ($model) {
            $model->id = (string) Uuid::generate(4);
            $model->role = 'ROLE_CLIENT';
            $model->sendEmailVerificationNotification();
            Mail::to($model->email)->send(new WelcomeMail($model));
        });
    }
    public function directions()
    {
        return $this->hasMany('App\Models\Direction');
    }
}
