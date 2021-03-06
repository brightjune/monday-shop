<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use phpDocumentor\Reflection\Types\Self_;

class Order extends Model
{

    use SoftDeletes;

    protected $table = 'orders';
    protected $fillable = ['uuid', 'total', 'status', 'address', 'user_id'];




    // 订单状态
    const UN_PAY_STATUS = 0;
    const PAY_STATUS = 1;
    const PAY_STATUSES = [
      self::UN_PAY_TYPE => '未支付',
      self::PAY_STATUS => '支付',
    ];

    // 支付类型
    const UN_PAY_TYPE = 0;
    const PAY_ZHIFUBAO_TYPE = 1;
    const PAY_WEIXIN_TYPE = 2;
    const PAY_TYPES = [
      self::UN_PAY_TYPE => '未支付',
      self::PAY_ZHIFUBAO_TYPE => '支付宝',
      self::PAY_WEIXIN_TYPE => '微信'
    ];
    

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public static function boot()
    {
        parent::boot();


        // 自动生成商品的 uuid， 拼音
        static::saving(function ($model) {

            if (is_null($model->no)) {
                $model->no = static::findAvailableNo($model->user_id);
            }
        });
    }

    /**
     * @param string $userId
     * @param int    $try
     * @return string
     * @throws \Exception
     */
    public static function findAvailableNo($userId = '000000000', $try = 5)
    {
        $prefix = date('YmdHis');
        $suffix = fixStrLength($userId, 9);

        for ($i = 0; $i < $try; ++ $i) {
            $no = $prefix . fixStrLength(random_int(0, 9999), 5) . $suffix;

            if (! self::query()->where('no', $no)->exists()) {
                return $no;
            }
        }

        throw new \Exception('流水号生成失败');
    }
}
