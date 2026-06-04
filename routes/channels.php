<?php
// routes/channels.php
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('apartments', function ($user) {
    return $user !== null;
});
