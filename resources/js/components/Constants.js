import { trans } from "laravel-vue-i18n";

export default Object.freeze({
    input_types: [
        "String",
        "Email",
        "Phone",
        "Color",
        "Integer",
        "Decimal",
        "Double",
        "Date",
        "Time",
        "DateTime",
        "Password",
        "Link",
        "Select"
    ],
    value_types: {
        String: { type: "text" },
        Select: { type: "select" },
        Email: { type: "email" },
        Phone: { type: "tel" },
        Color: { type: "color" },
        Integer: { type: "number", step: 1 },
        Decimal: { type: "number", step: 0.01 },
        Double: { type: "number", step: 0.0000000000000001 },
        Date: { type: "date" },
        Time: { type: "time" },
        DateTime: { type: "datetime-local" },
        Password: { type: "password" },
        Link: { type: "url" },
    },
    text_types: [
        "text",
        "email",
        "number",
        "tel",
        "url",
        "color",
        "datetime-local",
        "month",
        "password",
        "time",
        "url",
        "week",
    ],
    file_types : "application/vnd.rar, application/zip, application/gzip, application/x-7z-compressed, font/* ,audio/*, " +
        "video/*, text/*, image/svg+xml, application/sql, application/vnd.openxmlformats-officedocument.wordprocessingml.document, " +
        "application/vnd.oasis.opendocument.text, application/vnd.openxmlformats-officedocument.presentationml.presentation, " +
        "application/vnd.oasis.opendocument.presentation, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, " +
        "application/vnd.oasis.opendocument.spreadsheet, application/xml, application/msword, application/vnd.ms-excel, " +
        "application/vnd.ms-powerpoint, application/pdf",
    date_types: ["date", "datetime", "time"],
    booleans: ["checkbox", "radio"],
    medias : ['image', 'video', 'file'],
    quill_toolbar: [
        ["bold", "italic", "underline", "strike"], // toggled buttons
        ["blockquote", "code-block", "code"],
        ["link", "image", "formula"],

        [{ header: 1 }, { header: 2 }], // custom button values
        [{ list: "ordered" }, { list: "bullet" }],
        [{ script: "sub" }, { script: "super" }], // superscript/subscript
        [{ indent: "-1" }, { indent: "+1" }], // outdent/indent
        [{ direction: "rtbl" }], // text direction

        [{ size: ["small", false, "large", "huge"] }], // custom dropdown
        [{ header: [1, 2, 3, 4, 5, 6, false] }],

        [{ align: [] }],

        ["clean"], // remove formatting button
    ],
    allIcons: function(the_icons) {
        if(the_icons  == null) return [];
        return the_icons.map((icon) => icon.name)
    },
    formatCell: function (t, v) {
        try {
            let ic = "fa-solid icon_list fa-circle-";
            if (this.text_types.includes(t.attributes.type))
                v = v.charAt(0).toUpperCase() + v.slice(1);
            if (t.attributes.type === "price") v = `$${v.toFixed(2)}`;
            if (this.booleans.includes(t.attributes.type)) {
                v = v
                    ? `<i class="${ic}check success_icon"></i>`
                    : `<i class="${ic}xmark error_icon"></i>`;
            }
            if (t.attributes.type === "icon")
                v = `<i class="${v} icon_list"></i>`;
        } catch (e) {}
        return v;
    },
    formToJson: function (event) {
        let formJSON = {};
        const formData = new FormData(event.target);
        formData.forEach((value, key) => {
            if (key.includes("[]")) {
                if (formJSON[key] === undefined) formJSON[key] = [value];
                else formJSON[key].push(value);
                return;
            }
            return (formJSON[key] = value);
        });
        return formJSON;
    },
    reformatDateTime: function (dt) {
        if (dt === null || dt === undefined || dt === "null" || dt.length === 0)
            return trans("No data available");
        dt = new Date(dt);
        return `${dt.toLocaleDateString()} ${dt.toLocaleTimeString()}`;
    },
    formatLog: function (key, value) {
        if (["created_at", "updated_at", "deleted_at"].includes(key))
            return this.reformatDateTime(value);
        if (key === "icon") return `<i class="${value}"></i>`;
        return value;
    },
    reformatTitle: function (key) {
        return key.split("_").join(" ").toUpperCase();
    },
    isBool: function (type) {
        return this.booleans.includes(type);
    },
    isText: function (type) {
        return this.text_types.includes(type);
    },
    isObject: function (type) {
        return type === "object";
    },
    isMediaFile : function(type){
        return this.medias.includes(type);
    },
    formatClassName: function (classname) {
        classname = classname.split("\\");
        return classname[classname.length - 1];
    },
    fillUrlParameters : function(uri, key, value){
        let prefix = uri.includes('?') ? '&' : '?';
        return `${uri}${prefix}${key}=${encodeURI(value)}`;
    }
});
