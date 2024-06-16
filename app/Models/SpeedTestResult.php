<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $server_id
 * @property string $download_speed
 * @property string $upload_speed
 * @property string $timestamp
 * @property string $ping
 * @property string $user_isp
 * @property string $user_ip
 * @property int $packet_loss
 */
class SpeedTestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'download_speed',
        'upload_speed',
        'timestamp',
        'ping',
        'user_isp',
        'user_ip',
        'packet_loss'
    ];
}
