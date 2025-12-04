export default class AmazonPayFormUtil {
    static formToJson(form) {
        const formData = new FormData(form);
        const formJSON = {};

        formData.forEach((value, key) => {
            if (formJSON[key]) {
                if (!Array.isArray(formJSON[key])) {
                    formJSON[key] = [formJSON[key]];
                }
                formJSON[key].push(value);
            } else {
                formJSON[key] = value;
            }
        });

        return formJSON;
    }
}
