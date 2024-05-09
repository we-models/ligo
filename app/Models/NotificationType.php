<?php

namespace App\Models;

use App\Interfaces\BaseModelInterface;
use App\Properties\Prop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NotificationType extends BaseModel implements BaseModelInterface
{
    /**
     * @var string[]
     */
    public $fillable = ['name', 'background', 'color', 'sound'];

    /**
     * @var string
     */
    public string $singular = 'notification_type';

    /**
     * @var array|string[]
     */
    public array $sortable = ['id', 'background', 'color', 'sound'];

    /**
     * @var string
     */
    public $table = 'notification_types';

    public function __construct(string $query= "")
    {
        parent::__construct();
        $this->query = $query;
    }

    /**
     * @param bool $self
     * @return array[]
     */
    public function getFields(bool $self = false) : array
    {
        $has_business = self::getCurrentBusiness() != null;
        $response = [
            (new Prop('name', __('Name'), [], 6))->textInput(),
            (new Prop('sound', __('Sound'), [], 6, false))->textInput(),
            (new Prop('background', __('Background'), [], 6))->colorInput(),
            (new Prop('color', __('Color'), [], 6))->colorInput(),

        ];
        if($has_business){
            $response = array_merge(
                $response,[(new Prop('business', __('Business'), [], 12))->objectInput(new Business())]
            );
        }
        return $this->getMergedFields($response);
    }

    /**

     */
    public static function newObject(string $parameters = "") : array   {
        return [
            'id' =>0,
            'name' => '',
            'sound' => '',
            'background' => '#ffffff',
            'color' => '#000000',
            'business' => self::getCurrentBusiness(),
        ];
    }

    /**
     * @return BelongsToMany
     */
    public function business(): BelongsToMany
    {
        if( auth()->user()->hasAnyRole(ALL_ACCESS))  return $this->all_business();
        return $this->all_business()->where('code', '=',  session(BUSINESS_IDENTIFY));
    }

}
