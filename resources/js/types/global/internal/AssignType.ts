export interface AssignRowType {
    id: number;
    name: string;
    code: string;
    email: string;
    fcm_token: string | null;
    email_verified_at: Date | null;
    created_at: Date | null;
    updated_at: Date | null;
    deleted_at: Date | null;
    relations: AssignRelationType[];
}

export interface AssignRelationType {
    id: number;
    name: string;
    relation: boolean;
}
