<?php
class View {
    
    protected $variables = array();

    /** 
     * Set the variables to be used on the view
     * @param String $name  name of variable
     * @param String $value value of variable
     */
    function set($name = '', $value = '') {
        $this->variables[$name] = $value;
    }

    /**
     * Get the data from a file
     * @param  string $path
     * @return string
     */
    function get($path = '') {

        $string = '';

        if ( file_exists(TWOLEVEL.'/application/views/'.$path.'.html') ) {
            $string = @file_get_contents(TWOLEVEL.'/application/views/'.$path.'.html');
        }

        if ( file_exists(TWOLEVEL.'/application/views/'.$path.'.php') ) {
            $string = @file_get_contents(TWOLEVEL.'/application/views/'.$path.'.php');
        }

        return $string;
    }

    /**
     * Load in a view
     * @param  string $view 
     * @return file       
     */
    function load($view = '') {
        extract($this->variables);
        
        // Load header
        if ( file_exists( '../application/views/header.php' ) ) {
            @include ( '../application/views/header.php' );
        }

        // Load view
        if ( file_exists( '../application/views/' . $view . '.php' ) ) {
            @include ( '../application/views/' . $view . '.php' );
        }

        // Load footer
        if ( file_exists( '../application/views/footer.php' ) ) {
            @include ( '../application/views/footer.php' );
        }
        return $this;
    }

    function header($type = 'json', $data = null) {
        header('Content-Type: application/'.$type);
        if ( null !== $data ) {
            switch ($type) {
                case 'json':
                    echo json_encode($data);
                    break;
            }
        }
    }
}