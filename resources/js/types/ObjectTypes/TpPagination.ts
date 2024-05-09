/** TYPES */
import type {TpTheObject} from '@/types/ObjectTypes/TpTheObject'
import type {TpPaginationLink} from '@/types/ObjectTypes/TpPaginationLink'

export interface TpPagination {
    current_page: number;
    data: Array<TpTheObject>;
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: Array<TpPaginationLink>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}
