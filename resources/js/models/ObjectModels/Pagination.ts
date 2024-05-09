/** MODELS */
import TheObject from '@/models/ObjectModels/TheObject'
import PaginationLink from '@/models/ObjectModels/PaginationLink'

export default class Pagination {
    public current_page: number = 1;
    public data: Array<TheObject> = [];
    public first_page_url: string = "";
    public from: number | null = null;
    public last_page: number = 1;
    public last_page_url: string = "";
    public links: Array<PaginationLink> = [];
    public next_page_url: string | null = null;
    public path: string = "";
    public per_page: number = 10;
    public prev_page_url: string | null = null;
    public to: number = 0;
    public total: number = 0;
}
