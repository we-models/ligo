export interface PaginationType {
    current_page: number | null;
    data: any[] | null;
    first_page_url: string | null;
    from: number | null;
    last_page: number | null;
    last_page_url: string | null;
    links: Link[] | null;
    next_page_url: string | null;
    path: string | null;
    per_page: number | null;
    prev_page_url: string | null;
    to: number | null;
    total: number | null;
}

export interface Link {
    url: null | string;
    label: string | null;
    active: boolean | null;
}
