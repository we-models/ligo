/** TYPES */
import type {TpTheObject} from '@/types/ObjectTypes/TpTheObject'
import type {TpMediaImage} from "@/types/ObjectTypes/TpMediaImage";

export interface TpUser {
    id: number;
    enable: number;
    name: string;
    lastname: string;
    document_type: TpTheObject | null;
    ndocument: string;
    birthday: Date;
    ncontact: string;
    area: TpTheObject | null;
    email: string;
    code: string;
    created_at: Date;
    images: Array<TpMediaImage>
}
