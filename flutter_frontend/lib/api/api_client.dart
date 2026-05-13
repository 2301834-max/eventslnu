import 'dart:convert';

import 'package:flutter_smart_event/api/api_config.dart';
import 'package:flutter_smart_event/api/token_store.dart';
import 'package:http/http.dart' as http;

class ApiException implements Exception {
  ApiException(this.message, {this.statusCode});

  final String message;
  final int? statusCode;

  @override
  String toString() => 'ApiException(statusCode: $statusCode, message: $message)';
}

class ApiClient {
  ApiClient({
    required TokenStore tokenStore,
    http.Client? httpClient,
  })  : _tokenStore = tokenStore,
        _http = httpClient ?? http.Client();

  final TokenStore _tokenStore;
  final http.Client _http;

  Uri _uri(String path, [Map<String, String>? query]) {
    final normalized = path.startsWith('/') ? path : '/$path';
    return Uri.parse('${ApiConfig.baseUrl}$normalized').replace(queryParameters: query);
  }

  Future<Map<String, dynamic>> postJson(
    String path, {
    Map<String, dynamic>? body,
    bool auth = true,
  }) async {
    final headers = await _headers(auth: auth);
    final resp = await _http.post(
      _uri(path),
      headers: headers,
      body: body == null ? null : jsonEncode(body),
    );
    return _decode(resp);
  }

  Future<Map<String, dynamic>> getJson(
    String path, {
    Map<String, String>? query,
    bool auth = true,
  }) async {
    final headers = await _headers(auth: auth);
    final resp = await _http.get(_uri(path, query), headers: headers);
    return _decode(resp);
  }

  Future<Map<String, String>> _headers({required bool auth}) async {
    final headers = <String, String>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };
    if (auth) {
      final token = await _tokenStore.readToken();
      if (token != null && token.isNotEmpty) {
        headers['Authorization'] = 'Bearer $token';
      }
    }
    return headers;
  }

  Map<String, dynamic> _decode(http.Response resp) {
    final status = resp.statusCode;
    final text = resp.body;
    final isJson = resp.headers['content-type']?.contains('application/json') ?? false;

    Map<String, dynamic> json;
    if (text.isEmpty) {
      json = <String, dynamic>{};
    } else if (isJson) {
      final decoded = jsonDecode(text);
      json = decoded is Map<String, dynamic> ? decoded : <String, dynamic>{'data': decoded};
    } else {
      json = <String, dynamic>{'message': text};
    }

    if (status < 200 || status >= 300) {
      throw ApiException(
        (json['message'] ?? 'Request failed').toString(),
        statusCode: status,
      );
    }

    return json;
  }
}

