# 🚀 Form Request Param for Hyperf

一个强大的 HTTP 请求参数验证器与 `RequestParam` 映射工具。旨在解决控制器层与逻辑层的数据参数转换与验证问题，让你的代码更优雅、更健壮！✨

## ✨ 主要特性

- 🎯 **自动验证**：基于注解的声明式验证规则，由 `Hyperf` 自动触发。
- 🔄 **`RequestParam` 自动映射**：请求参数自动转换为强类型的 PHP 对象，拒绝弱类型数组地狱。
- 🧩 **嵌套对象支持**：支持无限层级的嵌套 `RequestParam` 验证与转换。
- 📦 **复杂类型兼容**：完美支持 `Union Types` (如 `RequestParam|array`)，智能判断并优先实例化对象。
- ♻️ **复用性强**：支持在控制器方法中自由组合多个 `RequestParam`，灵活应对不同业务场景。
- 📋 **数组处理**：支持对象数组 (`List<RequestParam>`) 和关联数组 (`Map<string, RequestParam>`) 的自动转换。
- 🛠 **开箱即用**：提供命令行工具快速生成 RequestParam 类。

## 📦 下载安装

```bash
composer require fatbit/form-request-param
```

## 🚀 快速上手

### 1. 创建 RequestParam 类
使用命令行工具快速生成：
```bash
php bin/hyperf gen:request-param User
```
执行后将在 `App\RequestParams` 目录下生成 `UserRequestParam` 类。

### 2. 定义验证规则与属性
使用 PHP 8.1+ 注解定义参数规则：

```php
namespace App\RequestParams;

use Fatbit\FormRequestParam\Abstracts\AbstractFormRequestParam;
use Fatbit\FormRequestParam\Annotations\FormRequestRule;
use Fatbit\FormRequestParam\Annotations\FormRequestArrayRule;

class UserRequestParam extends AbstractFormRequestParam 
{
    #[FormRequestRule('required|string|max:255', '姓名')]
    public string $name;
    
    #[FormRequestRule('required|integer|in:1,2', '性别')]
    public int $sex;
    
    // 支持别名映射：前端传 'username' -> 映射到后端 $account
    #[FormRequestRule('required|string|max:255', '账号', 'username')]
    public string $account;
    
    // 简单数组验证
    #[FormRequestRule('required|array', '标签')]
    #[FormRequestArrayRule('*', 'required|int|gt:0', '标签Id')]
    public array $tags;
}
```

### 3. 在控制器中使用
在控制器方法中直接注入即可自动触发验证。支持**组合复用**多个 RequestParam，例如将通用的 ID 验证提取为 `IdRequestParam`。

```php
namespace App\Controller;

use App\RequestParams\UserRequestParam;
use App\RequestParams\IdRequestParam;

class UserController extends AbstractController
{
    /**
     * 创建用户
     * 自动验证 UserRequestParam
     */
    public function create(UserRequestParam $requestParam)
    {
        // 验证通过后，$requestParam 已经自动填充好数据
        // 直接传入 Service 层，业务逻辑无需再处理参数验证
        return $this->success($this->service->create($requestParam));
    }
    
    /**
     * 修改用户
     * ✨ 支持复用：同时注入多个 RequestParam
     * 自动验证所有注入的参数对象，任何一个验证失败都会终止请求
     */
    public function modify(IdRequestParam $idParam, UserRequestParam $userParam)
    {
        // $idParam 负责验证 ID 存在性与格式
        // $userParam 负责验证用户信息的合法性
        return $this->success($this->service->modify($idParam->id, $userParam));
    }
}
```

---

## 💡 高级用法

### 1. 嵌套对象 (Nested objects)
支持直接引用其他的 `RequestParam` 类作为属性，自动递归验证与实例化。

```php
namespace App\RequestParams;

use Fatbit\FormRequestParam\Abstracts\AbstractFormRequestParam;
use Fatbit\FormRequestParam\Annotations\FormRequestRule;
use Fatbit\FormRequestParam\Annotations\FormRequestArrayRule;
class CreateOrderParam extends AbstractFormRequestParam 
{
    // 自动验证并实例化 UserInfoParam
    #[FormRequestRule('required', '用户信息')]
    public UserInfoParam $userInfo; 
    
    // 支持 Nullable
    #[FormRequestRule('nullable', '收货地址')]
    public ?AddressParam $address;
}
```

### 2. 联合类型与智能转换 (Union Types) ⚡️
完美支持 PHP 联合类型。当定义为 `RequestParam|array` 时，系统会**优先尝试实例化对象**，只有当实例化失败或数据不符合时才回退为数组。

```php
namespace App\RequestParams;

use Fatbit\FormRequestParam\Abstracts\AbstractFormRequestParam;
use Fatbit\FormRequestParam\Annotations\FormRequestRule;
use Fatbit\FormRequestParam\Annotations\FormRequestArrayRule;
class ProductParam extends AbstractFormRequestParam 
{
    // 优先转换为 SkuParam 对象，如果失败则保留为数组
    #[FormRequestRule('required', 'SKU信息')]
    public SkuParam|array $skuInfo;
    
    // 明确指定属性为 array，但在注解中定义转换为 RequestParam
    // 此时 propertyType 为 array，但 toVal 指向 RequestParam，会调用 RequestParam->toArray()
    // 默认 arrayField 为 false (或者不传)，表示直接转换当前字段
    #[FormRequestRule('required|array', '配置信息')]
    #[FormRequestArrayRule(ConfigParam::class)]
    public array $config;
    
    /**
     * @var AddressParam[]
     */
    // 2. 对象数组 (List Mode)：验证并转换每个元素为 AddressParam
    // 使用 FormRequestArrayRule 并传入类名，arrayField 为 true 代表列表模式
    #[FormRequestRule('array', '其他地址列表')]
    #[FormRequestArrayRule(AddressParam::class, arrayField: true)]
    public array $otherAddresses;

    /**
     * @var array<string, AddressParam>
     */
    // 3. 关联数组对象 (Map Mode)：针对特定 Key 转换
    // arrayField 为字符串键名 'company'
    #[FormRequestRule('array', '公司信息')]
    #[FormRequestArrayRule(AddressParam::class, arrayField: 'company')]
    public array $companyInfo; // $companyInfo['company'] 将被转换为 AddressParam 对象。
}
```

### 3. 数组对象列表 (List Mode) 📋
处理对象数组 `[UserRequestParam, UserRequestParam]`。

提供了专用的 `FormRequestListArrayRule` 注解，语法更简洁。

```php
namespace App\RequestParams;

use Fatbit\FormRequestParam\Abstracts\AbstractFormRequestParam;
use Fatbit\FormRequestParam\Annotations\FormRequestRule;
use Fatbit\FormRequestParam\Annotations\FormRequestListArrayRule;

class BatchCreateParam extends AbstractFormRequestParam 
{
    /**
     * @var UserRequestParam[]
     */
    #[FormRequestRule('required|array', '用户列表')]
    // 使用 FormRequestListArrayRule，默认数组索引模式
    // 等同于 #[FormRequestArrayRule(UserRequestParam::class, arrayField: true)]
    #[FormRequestListArrayRule(UserRequestParam::class)] 
    public array $userList;
}
```

### 4. 关联数组对象 (Map Mode) 🗺
处理特定 Key 的对象转换，例如 `{"old": UserRequestParam, "new": UserRequestParam}`。

提供了专用的 `FormRequestMappingArrayRule` 注解，明确指定映射键。

```php
namespace App\RequestParams;

use Fatbit\FormRequestParam\Abstracts\AbstractFormRequestParam;
use Fatbit\FormRequestParam\Annotations\FormRequestRule;
use Fatbit\FormRequestParam\Annotations\FormRequestMappingArrayRule;

class CompareParam extends AbstractFormRequestParam 
{
    /**
     * @var array<string, UserRequestParam>
     */
    #[FormRequestRule('array', '对比数据')]
    // 映射 old 字段 -> UserRequestParam
    #[FormRequestMappingArrayRule(UserRequestParam::class, 'old')]
    // 映射 new 字段 -> UserRequestParam
    #[FormRequestMappingArrayRule(UserRequestParam::class, 'new')]
    // 等同于 #[FormRequestArrayRule(UserRequestParam::class, arrayField: 'new')]
    public array $compareData;
}
```

## 📝 命令行工具

| 命令 | 描述 |
| --- | --- |
| `gen:request-param {name}` | 快速生成 RequestParam 类文件 |

---

## 🤝 贡献
欢迎提交 Issue 或 Pull Request！

License: MIT