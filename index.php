<?php
// 瑞思账户系统
$APPID = "";
$TOKEN = "";

// 处理API请求

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    // 保存配置处理
    $deviceId = $_POST['deviceId'] ?? '';
    $config = $_POST['config'] ?? '';
    
    // 获取用户信息
    $userInfo = json_decode(file_get_contents("https://api.3r60.top/v2/account/getUserInfo.php?token=".$TOKEN."&deviceId=".$deviceId), true);
    
    if ($userInfo['code'] !== 200) {
        echo json_encode(['success' => false, 'error' => '无效设备ID']);
        exit;
    }
    
    $userId = $userInfo['userInfo']['basicInfo']['userId'];
    
    // 创建用户目录
    $mappingFile = __DIR__.'/user/asfasjhcbas.json';
    $mappings = [];
    
    if (file_exists($mappingFile)) {
        $mappings = json_decode(file_get_contents($mappingFile), true);
    }
    
    if (!isset($mappings[$userId])) {
        $randomDir = substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz0123456789', 10)), 0, 10);
        $mappings[$userId] = $randomDir;
        file_put_contents($mappingFile, json_encode($mappings));
    }
    
    $userDir = __DIR__.'/user/'.$mappings[$userId];
    if (!file_exists($userDir)) {
        mkdir($userDir, 0777, true);
    }
    
    // 保存配置文件
    file_put_contents($userDir.'/config.json', $config);
    echo json_encode(['success' => true]);
    exit;
}
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'load') {
    // 加载配置处理
    $deviceId = $_GET['deviceId'] ?? '';
    
    $userInfo = json_decode(file_get_contents("https://api.3r60.top/v2/account/getUserInfo.php?token=".$TOKEN."&deviceId=".$deviceId), true);
    
    if ($userInfo['code'] !== 200) {
        echo json_encode(['success' => false, 'error' => '无效设备ID']);
        exit;
    }
    
    $userId = $userInfo['userInfo']['basicInfo']['userId'];
    
    // 获取用户目录
    $mappingFile = __DIR__.'/user/asfasjhcbas.json';
    if (!file_exists($mappingFile)) {
        echo json_encode([]);
        exit;
    }
    
    $mappings = json_decode(file_get_contents($mappingFile), true);
    if (!isset($mappings[$userId])) {
        echo json_encode([]);
        exit;
    }
    
    $configFile = __DIR__.'/user/'.$mappings[$userId].'/config.json';
    if (file_exists($configFile)) {
        $userConfig = json_decode(file_get_contents($configFile));
        $userConfig->id = $mappings[$userId];
        echo json_encode($userConfig);
    } else {
        echo json_encode([]);
    }
    exit;
}
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getNewDeviceId') {
    echo file_get_contents("https://api.3r60.top/v2/account/spawnDeviceID.php?app_id=".$APPID."&token=".$TOKEN."&ip=".$_SERVER['REMOTE_ADDR']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-Hans">

<head>
    <meta charset="UTF-8">
    <script>
    // RWUIv3 配置包-内嵌模式
    // Devloped In 2024/6/23 (PYLXU)

    // 站点配置
    let webTitle = "DSZ云服务";

    // 顶栏配置
    let defaultTitle = "DSZ云服务";
    let defaultNavItems = [];
    let defaultNavRightItems = [
        { text: 
        '<i class="bi bi-person-circle"></i>', 
        href: `javascript:createDialog('confirm', 'primary', '用户信息', '<span style="text-align:left" id="userInfo"></span><script>getUserInfo()<\/script>
        ,()=>{userLink()})` }];
    // 底栏默认值
    let defaultFooterLinks = [
    { text: 'Github', href: 'https://github.com/examaware/dsz-exam-showboard' },
    { text: 'QQ群', href: 'https://qm.qq.com/q/qACgG892am' },
    ];
    let defaultCopyright = '版权所有 © 2025 DSZ考试看板';

    // 引入真实定义包
    document.writeln( `<script src="https://assets.3r60.top/v3/package.js"><\/script>`);
    </script>
    <title>DSZ云服务</title>
    <style>
        label {
            font-weight: 800
        }

        .exam-info {
            display: flex;
            gap: 3px
        }
    </style>
</head>
<body>
    <topbar></topbar>
    <main style="max-width: 650px;margin-left:auto;margin-right:auto">
        <h2>DSZ云服务</h2>
        <card>
            <label for="id">您的ID</label>
            <input placeholder="未获取(首次保存配置后刷新获取)" class="textEditor textEditor-success mt-5" style="margin-bottom:5px" type="text" id="id" readonly="true">
        </card>
        <card>
            <label for="examName">考试名称</label>
            <input placeholder="本场考试的名称" class="textEditor textEditor-success mt-5" style="margin-bottom:5px" type="text" id="examName">
        </card>
        <card>
            <label for="message">提示信息</label>
            <input placeholder="考试时的提示信息" class="textEditor textEditor-success mt-5" style="margin-bottom:5px" type="text" id="message">
        </card>
        <card>
            <label for="room">考场号</label>
            <input placeholder="考试时显示的考场号" class="textEditor textEditor-success mt-5" style="margin-bottom:5px" type="text" id="room">
        </card>
        <card>
            <label>考试信息</label>
            <div id="examInfosContainer">
                <div class="exam-info">
                    <input class="textEditor textEditor-success mt-5" type="text" placeholder="科目名称">
                    <input class="textEditor textEditor-success mt-5" type="datetime-local">
                    <input class="textEditor textEditor-success mt-5" type="datetime-local">
                    <button style="flex:1;min-width: 65px;max-height: 44px;margin: auto;" class="btn btn-shadow btn-danger btn-md" onclick="removeExamInfo(this)">删除</button>
                </div>
            </div>
            <button class="btn btn-shadow btn-primary btn-md" onclick="addExamInfo()">添加考试信息</button>
        </card>
        <card>
            <label for="fileInput">导入配置</label>
            <input style="font-size: 12px;margin-top: 5px;" class="textEditor textEditor-success" type="file" id="fileInput" onchange="importConfig()">
        </card>
        <div class="card" style="display: none">
            <label>JSON 配置预览</label>
            <pre id="jsonPreview"></pre>
        </div>
        <div class="button-group">
            <div class="mb-20 mt-10">
                <button style="width: 100%;" class="btn btn-shadow btn-success btn-md" onclick="exportConfig()">保存到云端</button>
            </div>
        </div>
        <footer></footer>
    </main>

    <script>

        // 账户系统相关
        const app_id = '<?php echo $APPID ?>';

        function fetchDeviceID() {
            fetch(`./?action=getNewDeviceId`)
                .then(response => response.json())
                .then(data => {
                    if (data.code === 300) {
                        const deviceID = data.deviceID;
                        localStorage.setItem('deviceID', deviceID);
                        openAuthPage(deviceID);
                    } else {
                        createDialog('alert', 'danger', '授权错误', 'deviceid创建失败');
                    }
                });
        }

        function openAuthPage(deviceID) {
            const url = `https://user.3r60.top/auth/webAuth/?deviceid=${deviceID}&app_id=${app_id}`;
            window.open(url, '_blank');

            // 开始检测用户信息
            checkUserInfo();
        }

        function checkUserInfo() {
            dialogId = createDialog('diy', 'primary', '登录“DSZ云服务”', '等待用户授权...');
            const deviceID = localStorage.getItem('deviceID');
            if (!deviceID) return;

            const interval = setInterval(() => {
                fetch(`https://api.3r60.top/v2/account/getUserInfo.php?deviceId=${deviceID}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.code == 200) {
                            clearInterval(interval);
                            window.location.reload();
                        }
                    });
            }, 3000); // 每3秒检测一次
        }

        function getUserInfo() {
            const deviceID = localStorage.getItem('deviceID');

                fetch(`https://api.3r60.top/v2/account/getUserInfo.php?deviceId=${deviceID}`)
                .then(response => response.json())
                .then(data => {
                    if (data.code == 200) {
                        const userInfo = data.userInfo.basicInfo;
                        document.getElementById('userInfo').innerHTML = `
                            <p>用户ID: ${userInfo.userId}</p>
                            <p>用户名: ${userInfo.userName}</p>
                            <p>昵称: ${userInfo.nickName}</p>
                            <p>邮箱: ${userInfo.userMail}</p>
                            <p>点击确定退出登录</p>
                        `;
                    } else {
                        document.getElementById('userInfo').innerHTML = '未登录，点击确定前往登录';
                    }
                });
        }

        function logout() {
            const deviceID = localStorage.getItem('deviceID');
            if (!deviceID) return;

            fetch(`https://api.3r60.top/v2/account/setUserLogOut.php?deviceId=${deviceID}`)
                .then(response => response.json())
                .then(data => {
                    if (data.code == 200) {
                        localStorage.removeItem('deviceID');
                        window.location.reload();
                    } else {
                        createDialog('alert', 'danger', '退出登录错误', '退出登录失败');
                    }
                });
        }

        function isUserLoggedIn(showAlert = false, callback) {
            const deviceID = localStorage.getItem('deviceID');
            if (!deviceID) {
                if (showAlert) {
                    createDialog('alert', 'danger', '退出登录', '用户授权已过期', () => {
                        window.location.reload();
                    });
                }
                if (callback) callback(false);
                return false;
            }

            fetch(`https://api.3r60.top/v2/account/getUserInfo.php?deviceId=${deviceID}`)
                .then(response => response.json())
                .then(data => {
                    if (callback) callback(data.code == 200,data.userInfo);
                });
        }

        function userLink() {
            isUserLoggedIn(false, (loggedIn) => {
                if (loggedIn) {
                    logout();
                } else {
                    fetchDeviceID();
                }
            });
        }

        function checkLoginStatusPeriodically() {
            setInterval(() => {
                isUserLoggedIn(true, (loggedIn) => {
                    if (!loggedIn) {
                        const deviceID = localStorage.getItem('deviceID');
                        if (!deviceID) return;
                        $('main').remove()
                        createDialog('alert', 'danger', '退出登录', '用户授权已过期', () => {
                            window.location.reload();
                        });
                    }
                });
            }, 60000); // 每分钟检测一次
        }

        function checkLoginStatusOnLoad() {
            isUserLoggedIn(false, (loggedIn,info) => {
                if (!loggedIn) {
                    $('main').remove();
                    setTimeout(function() {
                        createDialog('alert', 'danger', '未登录', '您尚未登录，请登录后使用', ()=>{fetchDeviceID()});
                    }, 500);
                }
            });
        }

        // shift键判断
        let shiftKeyPressed = false;

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Shift') {
                shiftKeyPressed = true;
            }
        });

        document.addEventListener('keyup', function(event) {
            if (event.key === 'Shift') {
                shiftKeyPressed = false;
            }
        });


        // 启动定期检查登录状态
        checkLoginStatusPeriodically();

        // 页面加载完毕后检查登录状态
        document.addEventListener('DOMContentLoaded', () => {
            checkLoginStatusOnLoad();
        });



    // 本地配置上传
        function addExamInfo() {
            const container = document.getElementById('examInfosContainer');
            const newExamInfo = document.createElement('div');
            newExamInfo.className = 'exam-info';
            newExamInfo.innerHTML = `
                <input class="textEditor textEditor-success mt-5" type="text" placeholder="科目名称">
                <input class="textEditor textEditor-success mt-5" type="datetime-local">
                <input class="textEditor textEditor-success mt-5" type="datetime-local">
                <button style="flex:1;min-width: 65px;max-height: 44px;margin: auto;" class="btn btn-shadow btn-danger btn-md" onclick="removeExamInfo(this)">删除</button>
            `;
            container.appendChild(newExamInfo);
            // 为新添加的输入框绑定事件
            const inputs = newExamInfo.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('input', updateJsonPreview);
            });
            updateJsonPreview();
        }

        function removeExamInfo(button) {
            button.parentElement.remove();
            updateJsonPreview();
        }

        function clearConfig() {
            document.getElementById('examName').value = '';
            document.getElementById('message').value = '';
            document.getElementById('room').value = '';
            document.getElementById('examInfosContainer').innerHTML = `
                <div class="exam-info">
                    <input type="text" placeholder="科目名称">
                    <input type="datetime-local">
                    <input type="datetime-local">
                    <button onclick="removeExamInfo(this)">删除</button>
                </div>
            `;
            document.getElementById('jsonPreview').textContent = '';
        }

        function updateJsonPreview() {
            const examName = document.getElementById('examName').value;
            const message = document.getElementById('message').value;
            const room = document.getElementById('room').value;
            const examInfos = Array.from(document.querySelectorAll('#examInfosContainer .exam-info')).map(info => ({
                name: info.children[0].value,
                start: info.children[1].value,
                end: info.children[2].value
            }));

            const config = {
                examName,
                message,
                room,
                examInfos
            };

            document.getElementById('jsonPreview').textContent = JSON.stringify(config, null, 2);
        }

        async function loadCloudConfig() {
            try {
                const deviceId = localStorage.getItem('deviceID');
                if (!deviceId) {
                    console.log('未找到设备ID');
                    return;
                }
                
                const response = await fetch(`?action=load&deviceId=${encodeURIComponent(deviceId)}`);
                const config = await response.json();
                
                // 填充表单数据
                if (config.examName) {
                    document.getElementById('examName').value = config.examName;
                    document.getElementById('message').value = config.message;
                    document.getElementById('room').value = config.room;
                    document.getElementById('id').value = config.id;
                    
                    const container = document.getElementById('examInfosContainer');
                    container.innerHTML = '';
                    
                    config.examInfos.forEach(info => {
                        const div = document.createElement('div');
                        div.className = 'exam-info';
                        div.innerHTML = `
                            <input class="textEditor textEditor-success mt-5" type="text" placeholder="科目名称" value="${info.name}">
                            <input class="textEditor textEditor-success mt-5" type="datetime-local" value="${info.start}">
                            <input class="textEditor textEditor-success mt-5" type="datetime-local" value="${info.end}">
                            <button style="flex:1;min-width: 65px;max-height: 44px;margin: auto;" 
                                    class="btn btn-shadow btn-danger btn-md" 
                                    onclick="removeExamInfo(this)">删除</button>
                        `;
                        container.appendChild(div);
                        const inputs = div.querySelectorAll('input');
                        inputs.forEach(input => {
                            input.addEventListener('input', updateJsonPreview);
                        });
                    });
                    updateJsonPreview();
                }
            } catch (error) {
                console.error('加载配置失败:', error);
            }
        }

        async function saveToCloud() {
            try {
                const deviceId = localStorage.getItem('deviceID');
                if (!deviceId) {
                    alert('请先设置设备ID');
                    return;
                }
                
                const config = document.getElementById('jsonPreview').textContent;
                
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `deviceId=${encodeURIComponent(deviceId)}&config=${encodeURIComponent(config)}`
                });
                
                const result = await response.json();
                if (result.success) {
                    alert('配置保存成功！');
                } else {
                    alert('保存失败: ' + (result.error || '未知错误'));
                }
            } catch (error) {
                console.error('保存失败:', error);
                alert('保存配置时发生错误');
            }
        }

        // 初始化时加载配置
        document.addEventListener('DOMContentLoaded', () => {
            updateJsonPreview();
            loadCloudConfig();
        });

        // 修改导出配置函数
        function exportConfig() {
            saveToCloud(); // 直接调用云端保存
        }

        function importConfig(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const config = JSON.parse(e.target.result);
                    document.getElementById('examName').value = config.examName || '';
                    document.getElementById('message').value = config.message || '';
                    document.getElementById('room').value = config.room || '';

                    const container = document.getElementById('examInfosContainer');
                    container.innerHTML = ''; // Clear existing exam infos

                    config.examInfos.forEach(info => {
                        const newExamInfo = document.createElement('div');
                        newExamInfo.className = 'exam-info';
                        newExamInfo.innerHTML = `
                            <input type="text" value="${info.name}" placeholder="科目名称">
                            <input type="datetime-local" value="${info.start}">
                            <input type="datetime-local" value="${info.end}">
                            <button onclick="removeExamInfo(this)">删除</button>
                        `;
                        container.appendChild(newExamInfo);
                        const inputs = newExamInfo.querySelectorAll('input');
                        inputs.forEach(input => {
                            input.addEventListener('input', updateJsonPreview);
                        });
                    });

                    updateJsonPreview();
                } catch (error) {
                    alert('无法解析文件内容，请确保文件是一个有效的JSON格式。');
                }
            };
            reader.readAsText(file);
        }

        // 监听所有输入框的变化
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', updateJsonPreview);
        });

        // Initial JSON preview update
        updateJsonPreview();
    </script>
</body>
