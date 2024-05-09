/** MODELS */
import ObjectData from '@/models/ObjectModels/ObjectData'
import ObjectType from '@/models/ObjectModels/ObjectType'
import TheObject from '@/models/ObjectModels/TheObject'
import MediaFile from '@/models/ObjectModels/MediaFile'
import MediaImage from '@/models/ObjectModels/MediaImage'
import Role from "@/models/ObjectModels/Role";
import User from "@/models/ObjectModels/User";

/** TYPES */
import type {TpObjectTypeRelation} from '@/types/ObjectTypes/TpObjectTypeRelation'
import type {TpTheObject} from '@/types/ObjectTypes/TpTheObject'


export default class ObjectTypeRelation {
    public id: number = 0;
    public name: string = "";
    public slug: string = "";
    public type: string = 'unique';
    public type_relationship: string = 'object';
    public filling_method: string = "selection";
    public object_type: ObjectType = new ObjectType();
    public tab: number = 0;
    public relation: ObjectType = new ObjectType();
    public roles: Array<Role> = [];
    public description: string | null = "";
    public enable: boolean = true;
    public editable: boolean = true;
    public required: boolean = false;
    public order: number = 0;
    public width: number = 4;

    public created_at: Date = new Date();
    public data: ObjectData | null = null;
    public entity: TheObject | Array<TheObject> | MediaImage | MediaFile | User | Array<User> = null;
    public status: string = "relation";
    public structure: TheObject | null = null;


    constructor() {
    }

    static FromJSON(objTpRel: TpObjectTypeRelation) {
        const obj_type_rel = new ObjectTypeRelation();
        obj_type_rel.id = objTpRel.id;
        obj_type_rel.name = objTpRel.name;
        obj_type_rel.slug = objTpRel.slug;
        obj_type_rel.type = objTpRel.type;
        obj_type_rel.type_relationship = objTpRel.type_relationship;
        obj_type_rel.filling_method = objTpRel.filling_method;
        obj_type_rel.object_type = ObjectType.FromJSON(objTpRel.object_type);
        obj_type_rel.tab = objTpRel.tab;
        obj_type_rel.relation = ObjectType.FromJSON(objTpRel.relation);
        obj_type_rel.roles = objTpRel.roles;
        obj_type_rel.description = objTpRel.description;
        obj_type_rel.enable = objTpRel.enable == 1;
        obj_type_rel.editable = objTpRel.editable == 1;
        obj_type_rel.required = objTpRel.required == 1;
        obj_type_rel.order = objTpRel.order;
        obj_type_rel.width = objTpRel.width;
        obj_type_rel.created_at = objTpRel.created_at;

        obj_type_rel.data = ObjectData.FromJSON(objTpRel.data);
        obj_type_rel.entity = ObjectTypeRelation.getEntity(objTpRel.entity, objTpRel.slug);
        obj_type_rel.status = objTpRel.status;

        return obj_type_rel;
    }

    static getEntity(entity: any, slug: string): TheObject | Array<TheObject> | null {
        if (entity == null) return null;
        const ent = entity[slug];
        if (ent == null) return null;
        if (Array.isArray(ent)) return ent.map((nt: TpTheObject) => TheObject.FromJSON(nt));
        return TheObject.FromJSON(ent);
    }
}
