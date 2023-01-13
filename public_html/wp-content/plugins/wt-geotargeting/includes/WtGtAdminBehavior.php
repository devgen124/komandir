<?php
/**
 * Шаблон для админок
 * v.1.3
 */
class WtGtAdminBehavior
{
    public $data;

    /**
     * Проверяем и подключаем справочники с данными
     */
	function dataFilesInit(){
		if (!is_object($this->data)) $this->data = new WtGtDataFiles();
	}

    /**
     * Отображение элементов формы
     *
     * @param $args
     */
    function displaySettings($args) {
        extract( $args );

        $o = get_option( $option_name );

        if (!empty($option_name)) $name = $option_name . '[' . $id . ']';
        else $name = $id;

        switch ($type) {
            case 'text':
                $value = '';
                if (!empty($value_default)) $value = $value_default;
                if (isset($o[$id])) $value = esc_attr(stripslashes($o[$id]));

                echo '<input class="regular-text" type="text" id="' . $id . '" name="' . $name . '" value="' . $value . '" ';
                if (!empty($placeholder)) echo 'placeholder="' . $placeholder . '"';
                echo '/>';
                echo (!empty($desc)) ? "<br /><span class='description'>$desc</span>" : "";
                break;
            case 'number':
                $value = '';
                if (!empty($value_default)) $value = $value_default;
                if (isset($o[$id])) $value = esc_attr(stripslashes($o[$id]));

                echo '<input class="regular-text" type="number" id="' . $id . '" name="' . $name . '" value="' . $value . '" ';
                if (!empty($placeholder)) echo 'placeholder="' . $placeholder . '"';
                echo '/>';
                echo (!empty($desc)) ? "<br /><span class='description'>$desc</span>" : "";
                break;
            case 'password':
                $value = '';
                if (!empty($value_default)) $value = $value_default;
                if (isset($o[$id])) $value = esc_attr(stripslashes($o[$id]));

                echo '<input class="regular-text" type="password" id="' . $id . '" name="' . $name . '" value="' . $value . '" ';
                if (!empty($placeholder)) echo 'placeholder="' . $placeholder . '"';
                echo '/>';
                echo (!empty($desc)) ? "<br /><span class='description'>$desc</span>" : "";
                break;
            case 'textarea':
                $value = '';
                if (isset($o[$id])) $value = esc_attr( stripslashes($o[$id]) );
                echo "<textarea class='code large-text' cols='50' rows='10' type='text' id='$id' name='" . $name . "'>$value</textarea>";
                echo (!empty($desc)) ? "<br /><span class='description'>$desc</span>" : "";
                break;
            case 'checkbox':
                if (isset($o[$id]) && $o[$id] == 'on') $checked = " checked='checked'"; else $checked = '';
                echo "<label><input type='checkbox' id='$id' name='" . $name . "' $checked /> ";
                echo (!empty($desc)) ? $desc : "";
                echo "</label>";
                break;
            case 'select':
                echo "<select id='$id' name='" . $name . "'>";
                foreach($vals as $v=>$l){
                    $selected = '';
                    if (!empty($o[$id])) $selected = ($o[$id] == $v) ? "selected='selected'" : '';
                    echo "<option value='$v' $selected>$l</option>";
                }
                echo "</select>";
                echo (!empty($desc)) ? "<br /><span class='description'>$desc</span>" : "";
                break;
            case 'radio':
                echo "<fieldset>";
                foreach($vals as $v=>$l){
                    $checked = ($o[$id] == $v) ? "checked='checked'" : '';
                    echo "<label><input type='radio' name='" . $name . "' value='$v' $checked />$l</label><br />";
                }
                echo "</fieldset>";
                break;
            case 'info':
                echo '<p>'.$text.'</p>';
                break;
        }
    }

    /**
     * Очистка данных
     *
     * @param $options
     * @return mixed
     */
    function sanitizeCallback($options){
        if (is_array($options))
            foreach($options as $name => $val){
                if( $name == 'input')
                    $val = strip_tags($val);

                if($name == 'checkbox')
                    $val = intval($val);
            }
        return $options;
    }
}