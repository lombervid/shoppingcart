 <?php
 namespace ShoppingCart;

 /**
  * @version 1.0
  */

if ( !is_session_started() )    session_start();

/**
 * @package ShoppingCart
 */
class ShoppingCart {

    /**
     * @var $items Array of items
     */
    private $items;

    /**
     * @var $name Name of the $_SESSION
     */
    private $name;

    /**
     * Class constructor
     * 
     * @param string $name Name of the $_SESSION
     */
    function __construct( $name = 'shopping_cart' ) {

        if ( !is_string($name) ) {
            throw new Exception('Param $name must be string.', 1);            
        }

        $this->name = $name;
        $this->load();
    }
    
    /**
     * Get the items form the $_SESSION var
     */
    private function load() {
        if ( !empty( $_SESSION[$this->name] ) && is_array( $_SESSION[$this->name] ) ) {
            $this->items = $_SESSION[$this->name];
        } else {
            $this->items = array();            
        }        
    }

    /**
     * Save the items in the $_SESSION var
     */
    private function save() {
        $_SESSION[$name] = $this->items;
    }

    /**
     * Return the items
     * 
     * @return array Items in the shopping cart
     */
    public function get_items() {
        return $this->items;
    }

    /**
     * Clean the shopping cart
     */
    public function empty() {
        unset( $this->items );
        $this->save();
        $this->load();
    }

    /**
     * Checks if the cart is empty
     * 
     * @return  boolean Return true if there are items in the cart, otherwise false
     */
    public function is_empty() {
        return ($this->total_items > 0);
    }

    /**
     * Checks the total items in the cart
     * 
     * @return integer Total items in the cart
     */
    public function total_items() {
        return count( $this->items );
    }

    /**
     * Add an item in the cart
     * 
     * @param integer   $id         Item ID to add
     * @param integer   $amount     Amount to add
     * @param array     $fields     Extra fields
     */
    public function add( $id, $amount = 1, $fields = array() ) {

        if ( !is_integer( $id ) && !is_string( $id ) ) {
            throw new Exception('Params $id must be integer or string.', 1);            
        }

        if ( !is_numeric( $amount ) ) {
            throw new Exception('Params $amount must be integer.', 1); 
        }

        if ( !is_array( $fields ) ) {
            throw new Exception('Params $fields must be array.', 1); 
        }

        $u_id = md5( $id );

        if ( array_key_exists( $u_id, $this->items ) ) {
            $this->items[$u_id]['amount'] += intval( $amount );
        } else {
            $this->items[$u_id] = array(
                'id'        =>  $id,
                'amount'    =>  intval( $amount )
            );
            foreach ($fields as $field => $value) {
                $this->items[$u_id][$field] = $value;
            }
        }
        $this->save();
    }

    /**
     * Delete a item from the cart
     * 
     * @param  integer $id Item ID to delete
     */
    public function delete( $id ) {

        if ( !is_integer( $id ) && !is_string( $id ) ) {
            throw new Exception('Params $id must be integer or string.', 1);            
        }

        $u_id = md5( $id );

        if ( array_key_exists( $u_id, $this->items ) ) {
            unset( $this->items[$u_id] );
            $this->save();
        }
    }
} 