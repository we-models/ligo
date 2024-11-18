/** TYPES */
import type {TpRole} from "@/types/ObjectTypes/TpRole";


export default class Role {
    public id: number = 0;
    public name: string = "String";
    public description: string = "";
    public guard_name: string;
    "web";

    constructor() {
    }

    public static FromJSON(tr: TpRole): Role {
        let role = new Role();
        role.id = tr.id;
        role.name = tr.name;
        role.description = tr.description;
        role.guard_name = tr.guard_name;

        return role;
    }
}
