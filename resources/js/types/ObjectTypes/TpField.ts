/** TYPES */
import type {TpTheType} from "@/types/ObjectTypes/TpTheType";
import type {TpObjectTypeRelation} from "@/types/ObjectTypes/TpObjectTypeRelation";


export interface TpField {
    id: number;
    name: string;
    slug: string;
    object_type: number;
    layout: string;
    description: string;
    type: TpTheType;
    enable: number;
    required: number;
    editable: number;
    tab: number;
    format: string;
    show_tab_name: number;
    order: number;
    width: number;
    default: any;
    accept: string;
    created_at: Date;
    status: string;
    value: string;
    entity: any;
    fields: Array<TpObjectTypeRelation | TpField | any>
}
