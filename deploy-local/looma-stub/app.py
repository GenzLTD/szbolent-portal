"""
looma-stub: 占位 Flask 后端，仅用于本地仿真验证 nginx /v1/ 反代链路
注意：这是 stub，不是真的 looma-backend。正式对接 looma-zervi 时换 image。
"""
from flask import Flask, jsonify, request
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# /health 直接返 200，用于 docker healthcheck 和直接 curl
@app.route('/health')
def health():
    return jsonify({
        "status": "ok",
        "service": "looma-stub",
        "version": "0.1.0",
        "note": "本地仿真占位后端，非生产 looma"
    })

# /v1/* 路径对应 portal SPA 的 API 调用
@app.route('/v1/health')
def v1_health():
    return jsonify({
        "status": "ok",
        "service": "looma-stub",
        "version": "0.1.0",
        "path": request.path
    })

@app.route('/v1/chat', methods=['POST'])
def v1_chat():
    """占位 chat 接口，返一个 dummy 响应"""
    body = request.get_json(silent=True) or {}
    user_msg = body.get('message', '')
    return jsonify({
        "reply": f"[stub] 你说的是: {user_msg}",
        "model": "stub",
        "tokens_used": 0,
        "note": "占位接口，连真的 looma 时这里会被替换"
    })

@app.route('/v1/search')
def v1_search():
    """占位 search 接口"""
    q = request.args.get('q', '')
    return jsonify({
        "query": q,
        "results": [],
        "note": "占位接口，连 chromadb 后会有结果"
    })

# 根路径返一个简单说明
@app.route('/')
def root():
    return jsonify({
        "service": "looma-stub",
        "endpoints": ["/health", "/v1/health", "/v1/chat", "/v1/search"],
        "version": "0.1.0"
    })

if __name__ == '__main__':
    # listen 0.0.0.0:5200 让 docker 端口映射生效
    app.run(host='0.0.0.0', port=5200, debug=False)