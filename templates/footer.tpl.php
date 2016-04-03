        <!-- Main Footer -->
        <footer class="main-footer">
            <!-- To the right -->
            <div class="pull-right hidden-xs">
                Powered with ❤ by <a href="https://github.com/printempw/blessing-skin-server">Blessing Skin Server</a>.
            </div>
            <!-- Default to the left -->
            <strong>Copyright &copy; 2016 <a href="<?php echo Option::get('site_url'); ?>"><?php echo Option::get('site_name'); ?></a>.</strong> All rights reserved.
        </footer>

    </div><!-- ./wrapper -->

    <script type="text/javascript" src="../assets/libs/jquery/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="../assets/libs/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="../assets/libs/AdminLTE/dist/js/app.min.js"></script>
    <script type="text/javascript" src="../assets/libs/ply/ply.min.js"></script>
    <script type="text/javascript" src="../assets/libs/cookie.js"></script>
    <script type="text/javascript" src="../assets/js/utils.js"></script>
    <?php if (isset($data['script'])) echo $data['script']; ?>
    <script><?php echo Option::get('custom_js'); ?></script>
</body>
</html>
