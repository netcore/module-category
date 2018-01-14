import _ from 'lodash';

const FormMock = function (category) {
    let output = {
        icon: _.get(category, 'icon', ''),
        translations: {}
    };

    if (category && category.id) {
        output.id = category.id;
    }

    let languages = categoryModule.languages;
    let translations = _.keyBy(
        _.get(category, 'translations', []), 'locale'
    );

    _.each(languages, (language) => {
        let iso = language.iso_code;

        output.translations[iso] = {
            name: _.get(translations, iso + '.name', ''),
            slug: _.get(translations, iso + '.slug', '')
        };
    });

    return output;
};

export default FormMock;