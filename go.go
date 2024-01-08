package main

import (
    "embed"
    "fmt"
    "html/template"
    "io/fs"
    "io/ioutil"
    "math/rand"
    "net/http"
    "os"
    "path/filepath"
    "regexp"
    "strings"
    "time"
)

const (
    port = 9099     // 定义服务器端口号
    savePath = "_tmp"   // 定义保存文件的路径
)

//go:embed static/*
var static embed.FS // 定义用于嵌入静态文件的变量，上一行注释必须保留，删除了程序无法运行

func index(w http.ResponseWriter, r *http.Request) {
    path := r.URL.Path // 获取请求的 URL 路径
    path = strings.TrimPrefix(path, "/") // 去掉路径中的开头斜杠
    b, _ := regexp.MatchString(`^[a-zA-Z0-9_-]+$`, path)
    if !b || len(path) > 16 {
        jump(w, r) // 如果路径不符合指定的正则表达式规则，或者长度超过16，则跳转到 jump 函数处理
        return
    }

    filePath := filepath.Join(savePath, path) // 构建文件路径

    if r.Method == http.MethodPost { // 如果请求方法是 POST
        r.ParseForm()
        if !r.PostForm.Has("text") { // 检查解析后的表单数据中是否包含名为 "text" 的字段
            return
        }
        text := r.PostFormValue("text") // 获取表单中名为 "text" 的字段值
        if text == "" {
            _ = os.Remove(filePath) // 删除文件
        } else {
            _ = ioutil.WriteFile(filePath, []byte(text), 0666) // 写入文件
        }
        return
    }

    ua := r.Header.Get("user-agent") // 获取请求的 User-Agent 头部信息
    if r.URL.Query().Has("raw") || strings.HasPrefix(ua, "curl") || strings.HasPrefix(ua, "Wget") { // 如果是 curl/Wget 返回文件的内容
        w.Header().Set("Content-type", "text/plain; charset=UTF-8")
        c, _ := ioutil.ReadFile(filePath)
        _, _ = w.Write(c)
        return
    }

    var content string
    if _, e := os.Stat(filePath); os.IsNotExist(e) { // 检查文件的存在性并读取其内容
        content = ""
    } else {
        c, _ := ioutil.ReadFile(filePath)
        content = string(c)
    }

    tem, _ := template.ParseFS(static, "static/index.html") // 解析嵌入的静态文件中的模板
    _ = tem.Execute(w, struct {
        Title   string
        Content string
    }{
        Title:   path,
        Content: content,
    }) // 执行模板，并将数据传递给模板进行渲染
}

func jump(w http.ResponseWriter, r *http.Request) {
    http.Redirect(w, r, "/"+randStr(), http.StatusFound) // 重定向到随机生成的路径
}

func randStr() string {
    words := []byte("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789")
    str := ""
    rand.Seed(time.Now().UnixNano())
    for i := 0; i < 6; i++ {
        index := rand.Intn(len(words))
        str += string(words[index])
    }
    return str
}

func main() {
    web, _ := fs.Sub(static, "static") // 获取嵌入的静态文件
    f := http.FileServer(http.FS(web))

    http.Handle("/static/", http.StripPrefix("/static/", f)) // 处理静态文件请求
    http.HandleFunc("/", index) // 处理根路径请求   

    _ = http.ListenAndServe(fmt.Sprintf(":%d", port), nil) // 启动 HTTP 服务器
}
