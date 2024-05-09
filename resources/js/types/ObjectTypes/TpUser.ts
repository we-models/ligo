/** TYPES */
import type {TpTheObject} from '@/types/ObjectTypes/TpTheObject'
import type {TpMediaImage} from "@/types/ObjectTypes/TpMediaImage";

export interface TpUser {
    id: number;
    enable: number;
    name: string;
    lastname: string;
    ndocument: string;
    birthday: Date;
    ncontact: string;
    email: string;
    code: string;
    created_at: Date;
    images: Array<TpMediaImage>
}
