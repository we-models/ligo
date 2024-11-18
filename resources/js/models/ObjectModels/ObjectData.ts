/** TYPES */
import type {TpObjectData} from '@/types/ObjectTypes/TpObjectData'

export default class ObjectData {
    public type: string = "object";
    public name: string = "";
    public required: boolean = false;
    public multiple: boolean = false;

    constructor() {
    }

    static FromJSON(objDt: TpObjectData) {
        const object_data = new ObjectData();
        object_data.type = objDt.type;
        object_data.name = objDt.name;
        object_data.required = objDt.required;
        object_data.multiple = objDt.multiple;
        return object_data;
    }
}
