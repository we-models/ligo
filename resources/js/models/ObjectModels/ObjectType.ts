/** TYPES */
import type {TpObjectType} from '@/types/ObjectTypes/TpObjectType'

export default class ObjectType {
    public id: number = 0;
    public name: string = "";
    public prefix: string = "";
    public type: string = "post";
    public enable: boolean = true;
    public autogenerated_name: boolean = false;
    public editable_name: boolean = true;
    public access_code: string = "";
    public slug: string = "";
    public public: boolean = false;
    public show_image: boolean = true;
    public show_description: boolean = true;
    public description: string | null = "";

    constructor() {
    }

    static FromJSON(objType: TpObjectType): ObjectType {
        const object_type = new ObjectType();
        object_type.id = objType.id;
        object_type.name = objType.name;
        object_type.prefix = objType.prefix;
        object_type.type = objType.type;
        object_type.enable = objType.enable == 1;
        object_type.autogenerated_name = objType.autogenerated_name == 1;
        object_type.editable_name = objType.editable_name == 1;
        object_type.access_code = objType.access_code;
        object_type.slug = objType.slug;
        object_type.public = objType.public == 1;
        object_type.show_image = objType.show_image == 1;
        object_type.show_description = objType.show_description == 1;
        object_type.description = objType.description;

        return object_type;
    }
}
