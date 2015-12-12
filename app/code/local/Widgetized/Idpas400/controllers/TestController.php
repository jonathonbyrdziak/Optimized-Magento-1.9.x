<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CartController
 *
 * @author Jonathon
 */
class Widgetized_Idpas400_TestController extends Mage_Core_Controller_Front_Action {

    public function _auth() {
        if (!Mage::getSingleton('admin/session')->isLoggedIn()) {
//            $this->_redirect('/');
            return;
        }
    }

    /**
     * 
     * @return type
     */
    public function indexAction() {
        $this->_auth();
        $customerObj = Mage::getSingleton('customer/session')->getCustomer();
        ?>
        <h3>Test Actions</h3>
        <ul>
            <li><p><a href="<?php echo Mage::getBaseUrl() ?>externaldb/test/smtp">
                    SMTP Tester</a><br/>
                    </p></li>

            <li><p><a href="<?php echo Mage::getBaseUrl() ?>externaldb/test/recurring_status_update">
                        Sync Orders From ERP</a><br/>
                    updates the order status</p></li>

            <li><p><a href="<?php echo Mage::getBaseUrl() ?>externaldb/test/productsync">
                        Sync Products From ERP</a><br/>
                    This is a manual operation for syncing orders from the ERP</p></li>

            <li><p><a href="<?php echo Mage::getBaseUrl() ?>externaldb/test/shipping">
                        Shipping Discount Test</a><br/>
                    You must be logged in to run the shipping test.</p></li>

            <li><p><a href="<?php echo Mage::getBaseUrl() ?>externaldb/test/recurringorders">
                        Manually Run Recurring Orders</a><br/>
                    This is not an isolated test. It will run all recurring orders in the system. It is best to be logged out for this test. FYI, this process does take a few minutes.</p></li>

            <li><p><a href="<?php echo Mage::getBaseUrl() ?>externaldb/test/recurringtest">
                        Recurring Order Tests</a><br/>
                </p></li>

            <li><p><a href="<?php echo Mage::getBaseUrl() ?>externaldb/test/sendreminder">
                        Send Reminder Email</a><br/>
                </p></li>

            <li><p><a href="<?php echo Mage::getBaseUrl() ?>externaldb/test/gettaxclassid">
                        Get the tax class id</a><br/>
                </p></li>

            <li><p><a href="<?php echo Mage::getBaseUrl() ?>externaldb/test/insertuser">
                        Insert User into ERP</a><br/>
                    This test allows you to select a user to be inserrted into the ERP</p></li>
            <li><p><a href="<?php echo Mage::getBaseUrl() ?>externaldb/test/insertorder">
                        Insert Order into ERP</a><br/>
                    This test allows you to select an order to be inserrted into the ERP</p></li>

        </ul>
        <?php
    }

    public function smtpAction() {
        $this->_auth();

//        ignore_user_abort(true);
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        date_default_timezone_set('America/Los_Angeles');

        echo '<h3>recurring_status_update</h3>';
        echo "<p></p>";



        require_once 'Zend/Mail.php';
        require_once 'Zend/Mail/Transport/Smtp.php';

// Sender
        $your_name = 'Your name';
        $your_gmail = 'info@b4schools.com';
        $your_gmail_pass = 's8@4JP';

        // Recipient
        $send_to_name = 'Recipient';
        $send_to_email = 'jonathonbyrd@gmail.com';

        // Subject
        $mailSubject = 'Email test';
        // Body
        $textBody = 'This is the text of the email.';


        $configs = array(
            'hosts' => array(
                'EAST.EXCH028.serverdata.net',
                'scriptmail.intermedia.net',
                'mail028-2.exch028.serverdata.net',
                
                ),
            'ssl' => array('ssl','tls',false,'ssl/tls'),
            'port' => array('465','587','1025','25'),
        );

        foreach ($configs['hosts'] as $host) {
            foreach ($configs['ssl'] as $ssl) {
                foreach ($configs['port'] as $port) {

                    // SMTP server configuration
                    $smtpConfig = array(
                        'auth' => 'login',
                        'ssl' => $ssl,
                        'port' => $port,
                        'username' => 'info@b4schools.com',
                        'password' => 's8@4JP');

                    // Object of Zend_Mail_Transport_Smtp. You must use smtp.gmail.com
                    $transport = new Zend_Mail_Transport_Smtp($host, $smtpConfig);

                    //Create email
                    $mail = new Zend_Mail('UTF-8');
                    $mail->setFrom($your_gmail, $your_name);
                    $mail->addTo($send_to_email, $send_to_name);
                    $mail->setSubject($mailSubject);
                    $mail->setBodyText($textBody);

                    //Send
                    try {
                        $mail->send();
                        echo "<strong><br/> <br/> Email - message sent!";
                        echo "<br/> $host:$port $ssl</strong>";
                    } catch (Exception $e) {
                        echo "<br/> <br/> Failed to send email!";
                        echo "<br/> $host:$port $ssl";
                    }
                }
            }
        }

//        $sql = "SELECT HXTORN as ID  FROM SRBSOH";
//        $records = Mage::getSingleton('idpas400/db')->fetch_array($sql);
//        var_dump($records);
//        $erpOrder = Mage::getModel('idpas400/observer')->recurring_status_update();




        echo '<br/><br/><br/><a href="' . Mage::getBaseUrl() . 'externaldb/test/recurring_status_update">Reset</a> | ';
        echo '<a href="' . Mage::getBaseUrl() . 'externaldb/test">Back to Tests</a>';
    }

    /**
     * 
     */
    public function recurring_status_updateAction() {
        $this->_auth();
        echo '<h3>Updating processing orders from ERP</h3>';

        $messages = Mage::helper('idpas400')->check_order_status();
        echo json_encode($messages);

        echo '<br/><br/><br/><a href="' . Mage::getBaseUrl() . 'externaldb/test/recurring_status_update">Reset</a> | ';
        echo '<a href="' . Mage::getBaseUrl() . 'externaldb/test">Back to Tests</a>';
    }

    /**
     * 
     */
    public function recurringtestAction() {
        $this->_auth();

//        ignore_user_abort(true);
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        date_default_timezone_set('America/Los_Angeles');

        echo '<h3>test recurring orders</h3>';
        echo "<p></p>";

        $h = Mage::helper('recorder');
        $collection = $h->getAllSubscriptions();
        ?>
        <form>
            <label>Order to insert</label>
            <select name="orderid">
                <?php
                foreach ($collection as $user):
                    ?>
                    <option value="<?php echo $user->getId() ?>">
                        <?php echo $user->getId() ?>
                    </option>
                <?php endforeach ?>
            </select>
            <br/>

            <button type="submit">Submit</button>
        </form>
        <hr/>
        <?php
        if ($_GET) {
            $orderid = Mage::app()->getRequest()->getParam('orderid');
                $recurring = Mage::getModel('recorder/order')->load($i);
                if (!$recurring->getId())
                    continue;

                echo "<br/><br/><br/><b>Order ID: $i</b>";
                $recurring->placeOrder();

                echo '<br/><br/>subtotal ' . $recurring->getData('subtotal');
                echo '<br/>shipping_amount ' . $recurring->getData('shipping_amount');
                echo '<br/>tax_amount ' . $recurring->getData('tax_amount');
                echo '<br/>grand_total ' . $recurring->getData('grand_total');
        }

        echo '<br/><br/><br/><a href="' . Mage::getBaseUrl() . 'externaldb/test/recurringtest">Reset</a> | ';
        echo '<a href="' . Mage::getBaseUrl() . 'externaldb/test">Back to Tests</a>';
    }

    /**
     * 
     */
    public function gettaxclassidAction() {
        $this->_auth();

        echo '<h3>Get the tax class id</h3>';

        $h = Mage::helper('idpas400');
        ?>
        <form>
            <label>AvaTax Classification Code</label>
            <input type="text" name="tax_class" value="PC010000" />
            <br/>

            <label>Tax Classification Label</label>
            <select name="tax_category_select">
                <option>B4S</option>
                <option>JAN</option>
                <option>PAPER</option>
            </select> or 
            <input type="text" name="tax_category" />
            <br/>

            <button type="submit">Submit</button>
        </form>
        <hr/>
        <?php
        if ($_GET) {
            $tax_class = Mage::app()->getRequest()->getParam('tax_class', false);
            $tax_category = Mage::app()->getRequest()->getParam('tax_category', false);
            $tax_category = $tax_category ? $tax_category : Mage::app()->getRequest()->getParam('tax_category_select', false);

            $tax_class_id = $h->getTaxClassId($tax_category, $tax_class);
            echo '<br>Tax Class ID</b> ' . $tax_class_id;



            echo '<br/><br/><br/><br/><br/>';
        }

        echo '<br/><br/><br/><br/><br/><a href="' . Mage::getBaseUrl() . 'externaldb/test/gettaxclassid">Reset</a> | ';
        echo '<a href="' . Mage::getBaseUrl() . 'externaldb/test">Back to Tests</a>';
    }

    /**
     * 
     */
    public function subscriptionsAction() {
        $this->_auth();


        echo '<h3>Show Subscriptions</h3>';
        echo "<p>Test shows a list of subscriptions and their data.</p>";

        $h = Mage::helper('recorder');
        $h->debuggingCheckOrder = true;

        $collection = $h->getSubscriptions();

        foreach ($collection as $_order) {
            $_order = Mage::getModel('sales/order')->load($_order->getId());

            echo '<br/><br/><b>order id</b> ' . $_order->getId();
            echo '<br/><b>state</b> ' . $_order->getData('state');
            echo '<br/><b>recurring_start_date</b> ' . $_order->getData('recurring_start_date');
            echo '<br/><b>failed_attempt</b> ' . $_order->getData('failed_attempt');
            echo '<br/><b>is_recurring</b> ';
            var_dump($_order->getData('is_recurring'));
        }



        echo '<br/><br/><br/><br/><br/><a href="' . Mage::getBaseUrl() . 'externaldb/test/subscriptions">Reset</a> | ';
        echo '<a href="' . Mage::getBaseUrl() . 'externaldb/test">Back to Tests</a>';
    }

    public function insertorderAction() {
        $this->_auth();

        echo '<h3>Insert Order into ERP</h3>';
        echo "<p></p>";

        $collection = mage::getModel('sales/order')->getCollection();
        ?>
        <form>
            <label>Order to insert</label>
            <select name="orderid">
                <?php
                foreach ($collection as $user):
                    ?>
                    <option value="<?php echo $user->getId() ?>">
                        <?php echo $user->getData('increment_id') ?>
                    </option>
                <?php endforeach ?>
            </select>
            <br/>

            <button type="submit">Submit</button>
        </form>
        <hr/>
        <?php
        if ($_GET) {
            $h = Mage::helper('idpas400');
            $orderid = Mage::app()->getRequest()->getParam('orderid');
            
            echo "<h1>Inserting Order $orderid</h1>";
            
            $order = Mage::getModel('sales/order')->load($orderid);
            $customer = Mage::getModel('customer/customer')->load($order->getData('customer_id'));
            $order->setCustomer($customer);
            
//            $eEvent = new Varien_Event();
//            $eEvent->setOrder($order);
//            
//            $event = new Varien_Event_Observer();
//            $event->setEvent($eEvent);
//            
//            $observer = new Widgetized_Idpas400_Model_Observer();
//            $observer->sales_order_save_commit_after($event);
            
            //load the order and customer
//            // sync the customer and then the order
            $h->syncCustomer($customer, false, true);
            $results = $h->syncOrder($order, true);
        }

        echo '<br/><br/><br/><br/><br/><a href="' . Mage::getBaseUrl() . 'externaldb/test/insertuser">Reset</a> | ';
        echo '<a href="' . Mage::getBaseUrl() . 'externaldb/test">Back to Tests</a>';
    }

    public function insertuserAction() {
        $this->_auth();

        echo '<h3>Insert user into ERP</h3>';
        echo "<p></p>";

        $collection = mage::getModel('customer/customer')->getCollection();
        ?>
        <form>
            <label>User to insert</label>
            <select name="customerid">
                <?php
                foreach ($collection as $user):
                    $user = Mage::getModel('customer/customer')->load($user->getId());
                    ?>
                    <option value="<?php echo $user->getId() ?>">
                        <?php echo $user->getData('firstname') . ' ' . $user->getData('lastname') ?>
                    </option>
                <?php endforeach ?>
            </select>
            <br/>

            <button type="submit">Submit</button>
        </form>
        <hr/>
        <?php
        if ($_GET) {
            $h = Mage::helper('idpas400');
            $customerId = Mage::app()->getRequest()->getParam('customerid');
            $customer = Mage::getModel('customer/customer')->load($customerId);

            $results = $h->syncCustomer($customer, false, true);
        }

        echo '<br/><br/><br/><br/><br/><a href="' . Mage::getBaseUrl() . 'externaldb/test/insertuser">Reset</a> | ';
        echo '<a href="' . Mage::getBaseUrl() . 'externaldb/test">Back to Tests</a>';
    }

    /**
     * 
     */
    public function sendreminderAction() {
        $this->_auth();

        echo '<h3>Send Reminder Email</h3>';
        echo "<p>Enter the order number that you would like to send an email for. We will send it to your email address.</p>";

        $recurring = Mage::helper('recorder')->getAllSubscriptions();
        ?>
        <form>
            <label>Recurring Order Number</label>
            <select multiple name="orders[]">
                <?php foreach ($recurring as $order): ?>
                    <option value="<?php echo $order->getId() ?>"><?php echo $order->getId() ?></option>
                <?php endforeach ?>
            </select>
            <br/>

            <label>Your Email</label>
            <input name="email" value="jonathonbyrd@gmail.com"/>
            <br/>

            <button type="submit">Submit</button>
        </form>
        <hr/>
        <?php
        if ($_GET) {
            $orders = Mage::app()->getRequest()->getParam('orders');

            $h = Mage::helper('recorder');
            foreach ($orders as $id) {
                $order = Mage::getModel('recorder/order')->load($id);
                $h->sendEmailNotification($order, Mage::app()->getRequest()->getParam('email'));
            }
        }

        echo '<br/><br/><br/><br/><br/><a href="' . Mage::getBaseUrl() . 'externaldb/test/sendreminder">Reset</a> | ';
        echo '<a href="' . Mage::getBaseUrl() . 'externaldb/test">Back to Tests</a>';
    }

    /**
     * 
     */
    public function daysafterAction() {
        $this->_auth();

        echo '<h3>Process Order After Date</h3>';
        echo "<p>We're checking to see if an order date is given to this function, if that order should be processed.</p>";

        $date = date_parse(date('Y-m-d'));
        $date['day'] = $date['day'] + 1;
        $time = mktime(0, 0, 0, $date['month'], $date['day'], $date['year']);
        ?>
        <form>
            <label>Date in future</label>
            <input name="date" placeholder="Y-m-d" value="<?php echo date('Y-m-d', $time) ?>"/>
            <br/>

            <button type="submit">Submit</button>
        </form>
        <hr/>
        <?php
        if ($_GET) {
            $date = strtotime(Mage::app()->getRequest()->getParam('date'));

            $h = Mage::helper('recorder');
            $h->debuggingCheckOrder = true;

            echo '<table>';
            echo '<tr><td>Todays Date</td><td>' . date('Y-m-d') . '</td></tr>';
            echo "<tr><td>The Entered Order's Date</td><td>" . date('Y-m-d', $date) . '</td></tr>';

            $result = $h->dateHasPassedOrIsToday(strtotime($date));

            echo '<tr><td>Widgetized_Recorder_Helper_Data::dateHasPassedOrIsToday</td><td>' . (
            $result ? '<span style="color:green">TRUE</span>' : '<span style="color:red">FALSE</span>'
            ) . '</td></tr>';
            echo '</table>';
        }

        echo '<a href="' . Mage::getBaseUrl() . 'externaldb/test/daysafter">Reset</a> | ';
        echo '<a href="' . Mage::getBaseUrl() . 'externaldb/test">Back to Tests</a>';
    }

    /**
     * 
     */
    public function daysbeforeAction() {
        $this->_auth();

        echo '<h3>Checking Days Before</h3>';
        echo "<p>We're checking to see if the (y) date you have submitted is between (x) "
        . "and today</p>";

        $date = date_parse(date('Y-m-d'));
        $date['day'] = $date['day'] - 5;
        $time = mktime(0, 0, 0, $date['month'], $date['day'], $date['year']);
        ?>
        <form>
            <label>(y) Date before today (Y-m-d)</label>
            <input name="date" placeholder="Y-m-d" value="<?php echo date('Y-m-d', $time) ?>"/>
            <br/>

            <label>(x) Number of days within</label>
            <input name="before" value="5"/>
            <br/>

            <button type="submit">Submit</button>
        </form>
        <hr/>
        <?php
        if ($_GET) {
            $beforeTime = strtotime(Mage::app()->getRequest()->getParam('date'));
            $before = Mage::app()->getRequest()->getParam('before');

            $h = Mage::helper('recorder');
            $h->debuggingCheckOrder = true;

            echo '<table>';
            echo '<tr><td>Todays Date</td><td>' . date('Y-m-d') . '</td></tr>';
            echo '<tr><td>(y) Your Entered Date</td><td>' . date('Y-m-d', $beforeTime) . '</td></tr>';
            echo "<tr><td>(x) Days Before</td><td>$before</td></tr>";
            $result = $h->dateIsDaysBefore($beforeTime, $before);
            echo '<tr><td>Widgetized_Recorder_Helper_Data::dateIsDaysBefore</td><td>' . (
            $result ? '<span style="color:green">TRUE</span>' : '<span style="color:red">FALSE</span>'
            ) . '</td></tr>';
            echo '</table>';
        }

        echo '<br/><br/><br/><br/><br/><a href="' . Mage::getBaseUrl() . 'externaldb/test/daysbefore">Reset</a> | ';
        echo '<a href="' . Mage::getBaseUrl() . 'externaldb/test">Back to Tests</a>';
    }

    /**
     * 
     */
    public function recurringordersAction() {
        $this->_auth();

        $observer = Mage::helper('recorder')->place_recurring_orders();

        echo '<br/><br/><br/><br/><br/><a href="' . Mage::getBaseUrl() . 'externaldb/test/recurringorder">Reset</a> | ';
        echo '<a href="' . Mage::getBaseUrl() . 'externaldb/test">Back to Tests</a>';
    }

    /**
     * 
     */
    public function productsyncAction() {
        $this->_auth();

        $observer = Mage::getSingleton('idpas400/observer')->product_cron_sync();

        echo '<br/><br/><br/><br/><br/><a href="' . Mage::getBaseUrl() . 'externaldb/test/productsync">Reset</a> | ';
        echo '<a href="' . Mage::getBaseUrl() . 'externaldb/test">Back to Tests</a>';
    }

    /**
     * 
     * @return type
     */
    public function shippingAction() {
        $this->_auth();

        if (!Mage::helper('customer')->isLoggedIn()) {
            echo '<a href="' . Mage::getBaseUrl() . 'customer/account/login/" target="_blank">you must be logged in</a>';
            die;
        }

        echo '<h3>Showing Shipping Algorithm</h3>';
        echo '<p>Live shipping amounts are pulled from fedex. Live discounts are received from the ERP. '
        . 'Discounts are applied to the UPS rates. Zone information is ignored in this algorithm.</p>';
        ?>
        <form>
            <label>City</label>
            <input name="city"/>
            <br/>
            <label>State</label>
            <select defaultvalue="" id="region_id" name="region_id" title="" style="" class="form-control required-entry validate-select jcf-hidden">
                <option value="">Please select region, state or province</option>
                <option title="Alabama" value="1">Alabama</option><option title="Alaska" value="2">Alaska</option><option title="Arizona" value="4">Arizona</option><option title="Arkansas" value="5">Arkansas</option><option title="California" value="12">California</option><option title="Colorado" value="13">Colorado</option><option title="Connecticut" value="14">Connecticut</option><option title="Delaware" value="15">Delaware</option><option title="District of Columbia" value="16">District of Columbia</option><option title="Florida" value="18">Florida</option><option title="Georgia" value="19">Georgia</option><option title="Hawaii" value="21">Hawaii</option><option title="Idaho" value="22">Idaho</option><option title="Illinois" value="23">Illinois</option><option title="Indiana" value="24">Indiana</option><option title="Iowa" value="25">Iowa</option><option title="Kansas" value="26">Kansas</option><option title="Kentucky" value="27">Kentucky</option><option title="Louisiana" value="28">Louisiana</option><option title="Maine" value="29">Maine</option><option title="Maryland" value="31">Maryland</option><option title="Massachusetts" value="32">Massachusetts</option><option title="Michigan" value="33">Michigan</option><option title="Minnesota" value="34">Minnesota</option><option title="Mississippi" value="35">Mississippi</option><option title="Missouri" value="36">Missouri</option><option title="Montana" value="37">Montana</option><option title="Nebraska" value="38">Nebraska</option><option title="Nevada" value="39">Nevada</option><option title="New Hampshire" value="40">New Hampshire</option><option title="New Jersey" value="41">New Jersey</option><option title="New Mexico" value="42">New Mexico</option><option title="New York" value="43">New York</option><option title="North Carolina" value="44">North Carolina</option><option title="North Dakota" value="45">North Dakota</option><option title="Ohio" value="47">Ohio</option><option title="Oklahoma" value="48">Oklahoma</option><option title="Oregon" value="49">Oregon</option><option title="Pennsylvania" value="51">Pennsylvania</option><option title="Rhode Island" value="53">Rhode Island</option><option title="South Carolina" value="54">South Carolina</option><option title="South Dakota" value="55">South Dakota</option><option title="Tennessee" value="56">Tennessee</option><option title="Texas" value="57">Texas</option><option title="Utah" value="58">Utah</option><option title="Vermont" value="59">Vermont</option><option title="Virginia" value="61">Virginia</option><option title="Washington" value="62">Washington</option><option title="West Virginia" value="63">West Virginia</option><option title="Wisconsin" value="64">Wisconsin</option><option title="Wyoming" value="65">Wyoming</option></select>
            <br/>
            <label>Zip Code</label>
            <input name="zip"/>
            <br/>
            <?php
            $collection = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToFilter('type_id', array('eq' => 'simple'))
                    ->addAttributeToSelect('id')
                    ->addAttributeToSelect('name');
            ?>
            <label>Product</label>
            <select name="sku">
                <?php foreach ($collection as $product): ?>
                    <option value="<?php echo $product->getSku() ?>"><?php echo $product->getName() ?></option>
                <?php endforeach ?>
            </select>
            <br/>
            <label>Quantity</label>
            <input name="qty" value="1"/>
            <br/>
            <button type="submit">Submit</button>
        </form>
        <hr/>
        <?php
        if ($_GET) {
            $qty = Mage::app()->getRequest()->getParam('qty');
            echo '<table>';
            $productSkus = array(
                Mage::app()->getRequest()->getParam('sku') => $qty
            );
            echo '<tr><td>Product sku</td><td>' . Mage::app()->getRequest()->getParam('sku') . '</td></tr>';
            echo '<tr><td>Qty</td><td>' . $qty . '</td></tr>';

            $city = Mage::app()->getRequest()->getParam('city');
            echo "<tr><td>City</td><td>$city</td></tr>";

            $regionId = Mage::app()->getRequest()->getParam('region_id');
            echo "<tr><td>State</td><td>$regionId</td></tr>";

            $zip = Mage::app()->getRequest()->getParam('zip');
            echo "<tr><td>Zip Code</td><td>$zip</td></tr>";

            echo '<tr><td>Shipping Method</td><td>UPS Ground</td></tr>';
            $quote = Mage::helper('recorder')->createQuote($productSkus);

            $quote->getShippingAddress()
                    ->setCountryId('US')
                    ->setCity($city)
                    ->setPostcode($zip)
                    ->setRegionId($regionId)
                    ->setRegion('')
                    ->setCollectShippingRates(true);

            $address = $quote->getShippingAddress()->collectShippingRates();
            echo "<tr><td>Total Weight</td><td>" . $address->getData('weight') . "</td></tr>";
            echo "<tr><td>Individual Weight</td><td>" . ($address->getData('weight') / $qty) . "</td></tr>";

            $shippingRate = Mage::getModel('customer/session')->getData('shippingRate');
            echo "<tr><td><strong>UPS Live Rate</strong></td><td style='text-align:right'>$shippingRate</td></tr>";
            echo "<tr><td>Per Product Rate</td><td>" . ($shippingRate / $qty) . "</td></tr>";

            $shippingDiscount = Mage::getModel('customer/session')->getData('shippingDiscount');
            echo "<tr><td><strong>Discount From ERP (tim controlled)</strong></td><td style='text-align:right'>$shippingDiscount</td></tr>";
            echo "<tr><td>Per Product Discount</td><td>" . ($shippingDiscount / $qty) . "</td></tr>";

            echo "<tr><td><strong>Shipping Total</strong></td><td style='text-align:right'>" . $address->getData('shipping_amount') . "</td></tr>";

            echo '</table>';
        }

        echo '<br/><br/><br/><br/><br/><a href="' . Mage::getBaseUrl() . 'externaldb/test/shipping">Reset</a> | ';
        echo '<a href="' . Mage::getBaseUrl() . 'externaldb/test">Back to Tests</a>';
    }

}
