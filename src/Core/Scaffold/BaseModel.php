<?php

namespace GetCandy\Api\Core\Scaffold;

use Carbon\Carbon;
use GetCandy\Api\Core\Auth\Models\User;
use GetCandy\Api\Core\Traits\Hashids;
use GetCandy\Api\Model\Interfaces\BaseModelEventsInterface;
use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Model\Traits\BaseModelEvents;
use Prettus\Repository\Presenter\ModelFractalPresenter;


abstract class BaseModel extends Model implements BaseModelEventsInterface
{
    use Hashids;
    use BaseModelEvents;

    public $custom_attributes = [];
    ///////////////////////////////////////////

    /**
     * Indicates if the model should be auto set user_id.
     *
     * @var bool
     */
    protected $autoUserId = true;

    /**
     * Indicates if the model should be recorded ips.
     *
     * @var bool
     */
    protected $ips = true;

    /**
     * Indicates if the model should be recorded users.
     *
     * @var bool
     */
    protected $update_users = true;

    /**
     * Indicates timestamp is always saved in UTC timezone
     *
     * @var bool
     */
    protected $timestamp_always_save_in_utc = true;

    /**
     * Indicates timestamp is always get in user timezone
     *
     * @var bool
     */
    protected $timestamp_get_with_user_timezone = true;


    public function getSettingsAttribute()
    {
        $settings = app('api')->settings()->get($this->settings);
        if (! $settings) {
            return [];
        }

        return $settings->content;
    }

    public function setCustomAttribute($key, $value)
    {
        $this->custom_attributes[$key] = $value;

        return $this;
    }

    public function getCustomAttribute($key)
    {
        return $this->custom_attributes[$key] ?? null;
    }

    /**
     * Scope a query to only include enabled.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', '=', true);
    }

    /**
     * Scope a query to only include the default record.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('default', '=', true);
    }

    public function routes()
    {
        return $this->morphMany(Route::class, 'element');
    }

    ///////////////////////////////////////////////////

    /**
     * Get the auth instance.
     *
     * @return \Dingo\Api\Auth\Auth
     */
    protected function api_auth()
    {
        return app('Dingo\Api\Auth\Auth');
    }


    /**
     * Get current auth user
     *
     * @return User|null
     */
    public function getAuthUser()
    {
        $user = null;
        if ($this->api_auth()->check()) {
            $user = $this->api_auth()->user();
        } else if (\Auth::check()) {
            $user = \Auth::user();
        }
        return $user;
    }

    /**
     * Get current auth user_id
     *
     * @return mixed|null
     */
    public function getAuthUserId()
    {
        $user_id = null;
        $user = $this->getAuthUser();
        if ($user) {
            $user_id = $user->id;
        }
        return $user_id;
    }

    /**
     * Get current model's user_id
     *
     * @return mixed|null
     */
    public function getUserId()
    {
        return $this->id;
    }

    /**
     * Update the creation and update ips.
     *
     * @return void
     */
    protected function updateIps()
    {
        $ip = smart_get_client_ip();

        if (!$this->isDirty('updated_ip')) {
            $this->updated_ip = $ip;
        }

        if (!$this->exists && !$this->isDirty('created_ip')) {
            $this->created_ip = $ip;
        }
    }

    /**
     * Update the creation and update by users.
     *
     * @return void
     */
    protected function updateUsers()
    {
        $user_id = $this->getAuthUserId();
        if (!($user_id > 0)) {
            return;
        }

        if (!$this->isDirty('updated_by')) {
            $this->updated_by = $user_id;
        }

        if (!$this->exists && !$this->isDirty('created_by')) {
            $this->created_by = $user_id;
        }
    }

    /**
     * @return bool
     */
    public function isAuthUserOwner()
    {
        return $this->getAuthUserId() == $this->getUserId();
    }

    /**
     * @return Carbon
     */
    public function getNowTime()
    {
        return Carbon::now(app_timezone());
    }

    /**
     * @return Carbon
     */
    public function getNowUTCTime()
    {
        return Carbon::now('UTC');
    }

    /**
     * @return Carbon
     */
    public function getNowAuthUserTime()
    {
        return Carbon::now($this->getAuthUserDateTimezone());
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return User|null
     */
    public function getRelatedUser()
    {
        return $this->related_user;
    }


    /**
     * Return a timezone for all Datetime objects
     *
     * @return mixed
     */
    public function getAuthUserDateTimezone()
    {
        $user = $this->getAuthUser();
        if ($user && !empty($user->timezone)) {
            return $user->timezone;
        } else {
            return app_timezone();
        }
    }

    /**
     * Set a given attribute on the model.
     *
     *
     * @param  string $key
     * @param  mixed $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {

        if ($this->timestamp_always_save_in_utc) {
            // set to UTC only if Carbon
            if ($value instanceof Carbon) {
                $value->setTimezone('UTC');
            }
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param  string $key
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        $value = parent::getAttributeValue($key);

        if ($value instanceof Carbon && $this->timestamp_get_with_user_timezone) {
            $value->setTimezone($this->getAuthUserDateTimezone());
        }

        return $value;
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        if ($date instanceof Carbon && $this->timestamp_get_with_user_timezone) {
            $date->setTimezone($this->getAuthUserDateTimezone());
        }
        return $date->format($this->getDateFormat());
    }

    /**
     * @return mixed|Carbon
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @return mixed|Carbon
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

}
