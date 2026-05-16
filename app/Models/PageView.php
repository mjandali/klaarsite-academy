<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    use HasFactory;

    public const EVENT_PAGE_VIEW = 'page_view';
    public const EVENT_COURSE_VIEW = 'course_view';
    public const EVENT_LESSON_VIEW = 'lesson_view';
    public const EVENT_CHECKOUT_START = 'checkout_start';
    public const EVENT_PURCHASE_COMPLETED = 'purchase_completed';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'course_id',
        'lesson_id',
        'order_id',
        'event_type',
        'route_name',
        'path',
        'referrer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'device_type',
        'visitor_hash',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
