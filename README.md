 一个用于http请求参数的验证器, 目的是为了解决控制器层和逻辑层数据参数转换问题.

### 下载安装
~~~bash
composer require fatbit/form-request-param
~~~

### 使用
#### 一. 创建`form request param`类
执行下面命令, 执行完成后会在项目根目录下生成一个`App\RequestParams\UserRequestParam`类文件
~~~bash
php bin/hyperf gen:request-param user
~~~

#### 二. 设置自己的请求参数
    注解详细传参请查看注解类里的注释
~~~php
use Fatbit\FormRequestParam\Abstracts\AbstractFormRequestParam;
use Fatbit\FormRequestParam\Annotations\FormRequestRule;
use Fatbit\FormRequestParam\Annotations\FormRequestArrayRule;

class UserRequestParam extends AbstractFormRequestParam 
{
    #[FormRequestRule('required|string|max:255', '姓名')]
    public string $name;
    
    #[FormRequestRule('required|integer|in:1,2', '性别')]
    public int $sex;
    
    #[FormRequestRule(['required','integer'], '年龄')]
    public int $age;
    
    #[FormRequestRule('required|string|max:255', '账号', 'username')]
    public string $account;
    
    #[FormRequestRule('required|array', '标签')]
    #[FormRequestArrayRule('*', 'required|int|gt:0', '标签Id')]
    public array $tags;
    
}
~~~

#### 三. 引用请求参数
    可以引用多个`RequestParam`接收请求的时候会验证所有的字段规则
~~~php
class UserController
{
    public function create(UserRequestParam $requestParam)
    {
        return $this->success($this->service->create($requestParam));
    }
    
    
    public function modify(IdRequestParam $idRequestParam, UserRequestParam $requestParam)
    {
        return $this->success($this->service->modify($idRequestParam->id, $requestParam));
    }

}
~~~