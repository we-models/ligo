/** TYPES */
import type {TpField} from '@/types/ObjectTypes/TpField'
import type {TpMediaImage} from '@/types/ObjectTypes/TpMediaImage'
import type {TpObjectType} from '@/types/ObjectTypes/TpObjectType'

export interface TpTheObject {
    id: number;
    name: string;
    object_type: TpObjectType;
    description: string;
    images: Array<TpMediaImage>;
    created_at: Date;
    custom_fields: Array<TpField>;
    has_custom_fields: boolean;
}
