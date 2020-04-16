<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OauthAccessToken extends Model
{
    protected $table = "oauth_access_tokens";

    protected $fillable = [
        'id', 'user_id', 'client_id', 'name', 'scopes', 'revoked'
    ];

}
