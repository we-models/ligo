/** TYPES */
import type {TpPaginationLink} from '@/types/ObjectTypes/TpPaginationLink'

export default class PaginationLink {
    public active: boolean = false;
    public label: string = "";
    public url: string | null = null;


    constructor() {
    }

    static FromJSON(pgLink: TpPaginationLink): PaginationLink {
        const pagination_link = new PaginationLink();
        pagination_link.active = pgLink.active;
        pagination_link.label = pgLink.label;
        pagination_link.url = pgLink.url;
        return pagination_link;
    }

}
