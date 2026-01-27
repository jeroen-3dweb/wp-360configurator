<?php

function dwebps_setting_create_row($label, $description, $name, $value, $type = 'number')
{
    $checked = '';
    if ($type === 'checkbox') {
        $checked = $value ? 'checked' : '';
        $value   = 1;
    }

    return sprintf(
        '
            <div class="3dweb-ps__settings__row">
                    <div class="3dweb-ps__settings__label">
                        %s
                    </div>
                    <div class="3dweb-ps__settings-holder">
                        <input class="regular-text ltr" type="%s"
                               name="%s"
                               %s
                               value="%s"/>
                        <small class="3dweb-ps-settings__settings-holder__description">%s. </small>
                    </div>
                </div>
      ',
        esc_html($label), esc_attr($type), esc_attr($name), $checked, esc_attr($value), esc_html($description));
}

// create select box
function dwebps_setting_create_select($label, $description, $name, $value, $options)
{
    $select = '<select name="' . esc_attr($name) . '">';
    foreach ($options as $option) {
        $selected = $option['value'] === $value ? 'selected' : '';
        $select .= '<option value="' . esc_attr($option['value']) . '" ' . $selected . '>' . esc_html($option['label']) . '</option>';
    }
    $select .= '</select>';

    return sprintf(
        '
            <div class="3dweb-ps__settings__row">
                    <div class="3dweb-ps__settings__label">
                        %s
                    </div>
                    <div class="3dweb-ps__settings-holder">
                        %s
                        <small class="3dweb-ps-settings__settings-holder__description">%s. </small>
                    </div>
                </div>
      ',
        esc_html($label), $select, esc_html($description));
}