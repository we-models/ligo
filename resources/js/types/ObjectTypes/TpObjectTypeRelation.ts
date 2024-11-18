/** TYPES */
import type {TpObjectData} from '@/types/ObjectTypes/TpObjectData'
import type {TpObjectType} from '@/types/ObjectTypes/TpObjectType'
import type {TpTheObject} from "@/types/ObjectTypes/TpTheObject";

export interface TpObjectTypeRelation {
    id: number;
    name: string;
    slug: string;
    type: string;
    type_relationship: string;
    filling_method: string;
    object_type: TpObjectType;
    tab: number;
    relation: TpObjectType;
    roles: Array<any>;
    description: string | null;
    enable: number;
    editable: number;
    required: number;
    order: number;
    width: number;
    created_at: Date;
    data: TpObjectData;
    entity: any;
    status: string;
    structure: TpTheObject;
}
