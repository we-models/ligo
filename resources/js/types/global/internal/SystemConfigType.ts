export interface SystemConfigListOfTypesType {
    id: number;
    name: string;
    created_at: Date | null;
    updated_at: Date | null;
    deleted_at: Date | null;
}

export interface SystemConfigResponseType {
    placeholder: string | undefined;
    value: string | undefined;
    type: string | undefined;
    step: number | undefined;
}


export interface SystemConfigDataInformationType {
    id: number;
    name: string;
    description: string;
    default: string;
    type: any;
    custom_by_user: number;
    created_at: Date | undefined;
    updated_at: Date | undefined;
    deleted_at: Date | undefined;
    configuration: any;
    progress: number;
}
