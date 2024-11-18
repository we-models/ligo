/** TYPES */
import type {TpMediaImage} from '@/types/ObjectTypes/TpMediaImage'

export interface TpMediaFile {
    id: number;
    name: string
    size: number;
    extension: string;
    mimetype: string;
    user: number;
    url: string;
    permalink: string;
    created_at: Date;
    images: Array<TpMediaImage>;
}
