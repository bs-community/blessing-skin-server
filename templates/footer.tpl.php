        <!-- Main Footer -->
        <footer class="main-footer">
            <!-- To the right -->
            <div class="pull-right hidden-xs">
                Powered with ❤ by <a href="https://github.com/printempw/blessing-skin-server">Blessing Skin Server</a>.
            </div>
            <!-- Default to the left -->
            <strong>Copyright &copy; 2016 <a href="<?php echo Config::get('site_url'); ?>"><?php echo Config::get('site_name'); ?></a>.</strong> All rights reserved.
        </footer>

    </div><!-- ./wrapper -->

    <script type="text/javascript" src="../libs/jquery/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="../libs/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="../libs/AdminLTE/dist/js/app.min.js"></script>
    <script type="text/javascript" src="../libs/ply/ply.min.js"></script>
    <script type="text/javascript" src="../libs/cookie.js"></script>
    <script type="text/javascript" src="../assets/js/utils.js"></script>
    <?php if (isset($data['script'])) echo $data['script']; ?>
</body>
</html>
