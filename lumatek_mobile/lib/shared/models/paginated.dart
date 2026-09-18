import 'json_utils.dart';

/// Envoltura de la paginación de Laravel (`->paginate()`).
///
/// La usan los endpoints de historial de telemetría, historial de riego y
/// listado de alertas, que devuelven el paginador completo en la raíz.
class Paginated<T> {
  const Paginated({
    required this.items,
    required this.currentPage,
    required this.lastPage,
    required this.total,
    required this.perPage,
  });

  final List<T> items;
  final int currentPage;
  final int lastPage;
  final int total;
  final int perPage;

  bool get hasMore => currentPage < lastPage;
  bool get isEmpty => items.isEmpty;

  factory Paginated.fromJson(
    Map<String, dynamic> json,
    T Function(Map<String, dynamic>) itemBuilder,
  ) {
    final rawData = json['data'];
    final list = rawData is List
        ? rawData.map((item) => itemBuilder(asMap(item))).toList()
        : <T>[];

    return Paginated<T>(
      items: list,
      currentPage: asInt(json['current_page'], fallback: 1),
      lastPage: asInt(json['last_page'], fallback: 1),
      total: asInt(json['total'], fallback: list.length),
      perPage: asInt(json['per_page'], fallback: list.length),
    );
  }

  /// Une la página siguiente con lo que ya se tenía, para el scroll infinito.
  Paginated<T> merge(Paginated<T> next) {
    return Paginated<T>(
      items: [...items, ...next.items],
      currentPage: next.currentPage,
      lastPage: next.lastPage,
      total: next.total,
      perPage: next.perPage,
    );
  }

  static Paginated<T> empty<T>() => Paginated<T>(
        items: <T>[],
        currentPage: 1,
        lastPage: 1,
        total: 0,
        perPage: 15,
      );
}
